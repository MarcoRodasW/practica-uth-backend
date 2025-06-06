<?php

namespace UthApi\Controllers;

use UthApi\Services\TodoService;
use UthApi\Utils\Response;
use UthApi\Utils\JWT;

class TodoController
{
    private TodoService $todoService;

    public function __construct()
    {
        $this->todoService = new TodoService();
    }

    private function getUserFromToken(): ?object
    {
        $token = JWT::getTokenFromHeader();
        if (!$token) {
            Response::error('Authorization token required', 401);
        }

        $decoded = JWT::decode($token);
        if (!$decoded) {
            Response::error('Invalid or expired token', 401);
        }

        return $decoded;
    }

    public function create(): void
    {
        $user = $this->getUserFromToken();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Response::error('Invalid JSON data', 400);
        }

        $result = $this->todoService->create($user->user_id, $data);

        if ($result['success']) {
            Response::success([
                'todo_id' => $result['todo_id']
            ], $result['message'], 201);
        } else {
            Response::error('Failed to create todo', 400, $result['errors']);
        }
    }

    public function getAll(): void
    {
        $user = $this->getUserFromToken();

        $result = $this->todoService->getAll($user->user_id);
        Response::success($result['todos'], 'Todos retrieved successfully');
    }

    public function getById(int $id): void
    {
        $user = $this->getUserFromToken();

        $result = $this->todoService->getById($id, $user->user_id);

        if ($result['success']) {
            Response::success($result['todo'], 'Todo retrieved successfully');
        } else {
            Response::error('Todo not found', 404, $result['errors']);
        }
    }

    public function update(int $id): void
    {
        $user = $this->getUserFromToken();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Response::error('Invalid JSON data', 400);
        }

        $result = $this->todoService->update($id, $user->user_id, $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            $statusCode = in_array('Todo not found', $result['errors']) ? 404 : 400;
            Response::error('Failed to update todo', $statusCode, $result['errors']);
        }
    }

    public function delete(int $id): void
    {
        $user = $this->getUserFromToken();

        $result = $this->todoService->delete($id, $user->user_id);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            $statusCode = in_array('Todo not found', $result['errors']) ? 404 : 400;
            Response::error('Failed to delete todo', $statusCode, $result['errors']);
        }
    }
}
