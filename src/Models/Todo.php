<?php

namespace UthApi\Models;

use UthApi\Config\Database;
use PDO;

class Todo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $title, ?string $description, ?string $dueDate, int $userId, string $status = 'Pending'): bool
    {
        $stmt = $this->db->prepare("CALL CreateTodo(?, ?, ?, ?, ?)");

        return $stmt->execute([$title, $description, $dueDate, $userId, $status]);;
    }

    public function findAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare("CALL GetUserTodos(?)");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("CALL GetTodoById(?, ?)");
        $stmt->execute([$id, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function update(int $id, int $userId, ?string $title, ?string $description, ?string $dueDate, ?string $status): bool
    {
        $stmt = $this->db->prepare("CALL UpdateTodo(?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $userId, $title, $description, $dueDate, $status]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("CALL DeleteTodo(?, ?)");
        return $stmt->execute([$id, $userId]);
    }
}
