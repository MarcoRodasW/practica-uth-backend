<?php

namespace UthApi\Controllers;

use UthApi\Services\AuthService;
use UthApi\Utils\Response;
use UthApi\Utils\JWT;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
        }

        $result = $this->authService->register($data);

        if ($result['success']) {
            Response::success([], $result['message'], 201);
        } else {
            Response::error('Registration failed', 400, $result['errors']);
        }
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
        }

        $result = $this->authService->login($data);

        if ($result['success']) {
            Response::success([
                'token' => $result['token'],
                'user' => $result['user']
            ], 'Login successful');
        } else {
            Response::error('Login failed', 401, $result['errors']);
        }
    }

    public function updateProfile(): void
    {
        $token = JWT::getTokenFromHeader();
        if (!$token) {
            Response::error('Authorization token required', 401);
        }

        $decoded = JWT::decode($token);
        if (!$decoded) {
            Response::error('Invalid or expired token', 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            Response::error('Invalid JSON data', 400);
        }

        $result = $this->authService->updateProfile($decoded->user_id, $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error('Profile update failed', 400, $result['errors']);
        }
    }
}
