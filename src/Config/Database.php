<?php

namespace UthApi\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static array $connectionInfo = [];

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'];
                $dbname = $_ENV['DB_NAME'];
                $username = $_ENV['DB_USER'];
                $password = $_ENV['DB_PASS'];

                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10, // 10 second timeout
                ]);

                // Store connection info for health checks
                self::$connectionInfo = [
                    'host' => $host,
                    'database' => $dbname,
                    'username' => $username,
                    'connected_at' => date('Y-m-d H:i:s')
                ];
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function getConnectionInfo(): array
    {
        return self::$connectionInfo;
    }

    public static function testConnection(): bool
    {
        try {
            $db = self::getConnection();
            $stmt = $db->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
        self::$connectionInfo = [];
    }
}
