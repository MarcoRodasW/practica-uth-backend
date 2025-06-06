<?php

namespace UthApi\Services;

use UthApi\Models\Todo;
use UthApi\Utils\Validator;

class TodoService
{
    private Todo $todoModel;

    public function __construct()
    {
        $this->todoModel = new Todo();
    }

    public function create(int $userId, array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = "Title is required";
        } elseif (strlen($data['title']) > 255) {
            $errors[] = "Title must not exceed 255 characters";
        }

        if (!empty($data['dueDate']) && !Validator::validateDate($data['dueDate'])) {
            $errors[] = "Invalid due date format (YYYY-MM-DD HH:MM:SS)";
        }

        if (!empty($data['status']) && !Validator::validateStatus($data['status'])) {
            $errors[] = "Invalid status. Must be: Pending, InProgress, or Completed";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $todoId = $this->todoModel->create(
            Validator::sanitizeString($data['title']),
            !empty($data['description']) ? Validator::sanitizeString($data['description']) : null,
            !empty($data['dueDate']) ? $data['dueDate'] : null,
            $userId,
            $data['status'] ?? 'Pending'
        );

        if ($todoId) {
            return ['success' => true, 'todo_id' => $todoId, 'message' => 'Todo created successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to create todo']];
    }

    public function getAll(int $userId): array
    {
        $todos = $this->todoModel->findAllByUser($userId);
        return ['success' => true, 'todos' => $todos];
    }

    public function getById(int $id, int $userId): array
    {
        $todo = $this->todoModel->findById($id, $userId);

        if (!$todo) {
            return ['success' => false, 'errors' => ['Todo not found']];
        }

        return ['success' => true, 'todo' => $todo];
    }

    public function update(int $id, int $userId, array $data): array
    {
        $errors = [];

        if (!empty($data['title']) && strlen($data['title']) > 255) {
            $errors[] = "Title must not exceed 255 characters";
        }

        if (!empty($data['dueDate']) && !Validator::validateDate($data['dueDate'])) {
            $errors[] = "Invalid due date format (YYYY-MM-DD HH:MM:SS)";
        }

        if (!empty($data['status']) && !Validator::validateStatus($data['status'])) {
            $errors[] = "Invalid status. Must be: Pending, InProgress, or Completed";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if todo exists and belongs to user
        $existingTodo = $this->todoModel->findById($id, $userId);
        if (!$existingTodo) {
            return ['success' => false, 'errors' => ['Todo not found']];
        }

        $success = $this->todoModel->update(
            $id,
            $userId,
            !empty($data['title']) ? Validator::sanitizeString($data['title']) : null,
            isset($data['description']) ? Validator::sanitizeString($data['description']) : null,
            $data['dueDate'] ?? null,
            $data['status'] ?? null
        );

        if ($success) {
            return ['success' => true, 'message' => 'Todo updated successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to update todo']];
    }

    public function delete(int $id, int $userId): array
    {
        $existingTodo = $this->todoModel->findById($id, $userId);
        if (!$existingTodo) {
            return ['success' => false, 'errors' => ['Todo not found']];
        }

        $success = $this->todoModel->delete($id, $userId);

        if ($success) {
            return ['success' => true, 'message' => 'Todo deleted successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to delete todo']];
    }
}
