<?php
declare(strict_types=1);

/* Pemakaian: EVOTING_DB_NAME=evoting_osis_gedeg_test php database/load_test.php --users=200 */

if (PHP_SAPI !== 'cli' || !function_exists('curl_init')) {
    exit("Script memerlukan CLI PHP dengan ekstensi cURL.\n");
}

$database = getenv('EVOTING_DB_NAME') ?: '';
if ($database !== 'evoting_osis_gedeg_test') {
    exit("Untuk keamanan, script hanya boleh memakai database evoting_osis_gedeg_test.\n");
}

$requestedUsers = 200;
foreach ($argv as $argument) {
    if (preg_match('/^--users=(\d+)$/', $argument, $matches)) {
        $requestedUsers = (int) $matches[1];
    }
}
$requestedUsers = max(1, min(1360, $requestedUsers));

require_once __DIR__ . '/../config/database.php';

$initialVotes = (int) $pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn();
$initialUsed = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status_vote = 'sudah'")->fetchColumn();
$initialCandidateTotal = (int) $pdo->query('SELECT COALESCE(SUM(total_votes), 0) FROM candidates')->fetchColumn();

$codes = $pdo->query("SELECT username FROM users WHERE status_vote = 'belum' ORDER BY id LIMIT {$requestedUsers}")
    ->fetchAll(PDO::FETCH_COLUMN);
if (count($codes) < $requestedUsers) {
    exit("Kode peserta yang belum digunakan tidak cukup untuk pengujian.\n");
}

$baseUrl = 'http://127.0.0.1:8090';

function request(string $url, ?array $post = null, string $cookie = ''): array
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_COOKIE => $cookie,
    ]);
    if ($post !== null) {
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $response = curl_exec($handle);
    if ($response === false) {
        $error = curl_error($handle);
        curl_close($handle);
        throw new RuntimeException($error);
    }
    $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    return [$status, substr($response, 0, $headerSize), substr($response, $headerSize)];
}

function csrf(string $html): string
{
    if (!preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $html, $matches)) {
        throw new RuntimeException('Token CSRF tidak ditemukan.');
    }
    return $matches[1];
}

function sessionCookie(string $headers): string
{
    if (!preg_match('/^Set-Cookie:\s*(PHPSESSID=[^;]+)/mi', $headers, $matches)) {
        throw new RuntimeException('Cookie sesi tidak ditemukan.');
    }
    return $matches[1];
}

$voteRequests = [];
$setupFailures = 0;
$setupErrors = [];
foreach ($codes as $code) {
    $stage = 'membuka login';
    try {
        [, $headers, $html] = request($baseUrl . '/login.php');
        $cookie = sessionCookie($headers);
        $loginToken = csrf($html);
        $stage = 'mengirim login';
        [$status, $loginHeaders] = request($baseUrl . '/login.php', ['csrf_token' => $loginToken, 'username' => $code], $cookie);
        if ($status !== 302) {
            throw new RuntimeException('Login tidak mengarahkan ke halaman voting.');
        }
        $cookie = sessionCookie($loginHeaders);
        $stage = 'membuka voting';
        [, , $voteHtml] = request($baseUrl . '/vote.php', null, $cookie);
        $voteRequests[] = ['cookie' => $cookie, 'token' => csrf($voteHtml)];
    } catch (Throwable $exception) {
        $setupFailures++;
        if (count($setupErrors) < 3) {
            $setupErrors[] = $stage . ': ' . $exception->getMessage();
        }
    }
}

$multi = curl_multi_init();
$handles = [];
foreach ($voteRequests as $index => $data) {
    $handle = curl_init($baseUrl . '/vote.php');
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'csrf_token' => $data['token'],
            'candidate_id' => ($index % 3) + 1,
        ]),
        CURLOPT_COOKIE => $data['cookie'],
    ]);
    curl_multi_add_handle($multi, $handle);
    $handles[] = $handle;
}

do {
    $state = curl_multi_exec($multi, $running);
    if ($running > 0) {
        curl_multi_select($multi, 1.0);
    }
} while ($running > 0 && $state === CURLM_OK);

$voteFailures = 0;
$voteFailureStatuses = [];
foreach ($handles as $handle) {
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    if ($status !== 302) {
        $voteFailures++;
        $label = $status === 0 ? 'timeout_or_connection_error' : (string) $status;
        $voteFailureStatuses[$label] = ($voteFailureStatuses[$label] ?? 0) + 1;
    }
    curl_multi_remove_handle($multi, $handle);
    curl_close($handle);
}
curl_multi_close($multi);

$votes = (int) $pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn();
$used = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status_vote = 'sudah'")->fetchColumn();
$candidateTotal = (int) $pdo->query('SELECT COALESCE(SUM(total_votes), 0) FROM candidates')->fetchColumn();
$expectedTotal = count($codes);
$passed = $setupFailures === 0
    && $voteFailures === 0
    && $votes === $initialVotes + $expectedTotal
    && $used === $initialUsed + $expectedTotal
    && $candidateTotal === $initialCandidateTotal + $expectedTotal;

echo json_encode([
    'requested_users' => count($codes),
    'initial_votes' => $initialVotes,
    'login_setup_failures' => $setupFailures,
    'login_setup_errors' => $setupErrors,
    'vote_failures' => $voteFailures,
    'vote_failure_statuses' => $voteFailureStatuses,
    'votes' => $votes,
    'users_marked_voted' => $used,
    'candidate_vote_total' => $candidateTotal,
    'passed' => $passed,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;

exit($passed ? 0 : 1);