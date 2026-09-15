<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dbHost = 'localhost';
            $dbName = 'evoting_osis_gedeg';
            $dbUser = 'root';
            $dbPass = '';
            $dbCharset = 'utf8mb4';

            $configFile = __DIR__ . '/../../config/database.php';
            if (file_exists($configFile)) {
                include $configFile;
            }

            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // In connection method, let exceptions bubble up so they can be handled gracefully
            self::$pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        }

        return self::$pdo;
    }
}
