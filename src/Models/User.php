<?php

namespace UthApi\Models;

use UthApi\Config\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $username, string $email, string $passwordHash): bool
    {
        try {
            $stmt = $this->db->prepare("CALL CreateUser(?, ?, ?)");
            return $stmt->execute([$username, $email, $passwordHash]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("CALL GetUserByEmail(?)");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function updateProfile(int $id, ?string $username, ?string $email, ?string $passwordHash): bool
    {
        try {
            $stmt = $this->db->prepare("CALL UpdateUserProfile(?, ?, ?, ?)");
            return $stmt->execute([$id, $username, $email, $passwordHash]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function emailExists(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function usernameExists(string $username, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
