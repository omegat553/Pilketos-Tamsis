<?php
declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, status_vote FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, status_vote FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function countTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countVoted(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE status_vote = 'sudah'")->fetchColumn();
    }

    public function getPaginated(int $page, int $limit, string $search, string $status): array
    {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT id, username, status_vote, created_at FROM users WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND username LIKE ?';
            $params[] = '%' . $search . '%';
        }

        if ($status === 'belum' || $status === 'sudah') {
            $sql .= ' AND status_vote = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY id ASC LIMIT ? OFFSET ?';
        
        $stmt = $this->db->prepare($sql);
        
        $idx = 1;
        foreach ($params as $param) {
            $stmt->bindValue($idx++, $param);
        }
        $stmt->bindValue($idx++, $limit, \PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered(string $search, string $status): int
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND username LIKE ?';
            $params[] = '%' . $search . '%';
        }

        if ($status === 'belum' || $status === 'sudah') {
            $sql .= ' AND status_vote = ?';
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAllCodes(): array
    {
        return $this->db->query('SELECT username, status_vote FROM users ORDER BY id ASC')->fetchAll();
    }

    public function clearAndInsert(array $codes): bool
    {
        try {
            $this->db->beginTransaction();

            // Clear all votes
            $this->db->exec('DELETE FROM votes');
            // Reset candidates vote count
            $this->db->exec('UPDATE candidates SET total_votes = 0');
            // Clear all users
            $this->db->exec('DELETE FROM users');

            // Insert new users
            $stmt = $this->db->prepare("INSERT INTO users (username, status_vote) VALUES (?, 'belum')");
            foreach ($codes as $code) {
                $stmt->execute([$code]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
