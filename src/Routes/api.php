<?php

use UthApi\Controllers\AuthController;
use UthApi\Controllers\HealthController;
use UthApi\Controllers\TodoController;

function handleRoutes(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = preg_replace('#^/api/v1#', '', $path);

    // Health check routes (no authentication required)
    if ($path === '/health' && $method === 'GET') {
        (new HealthController())->healthCheck();
    } elseif ($path === '/health/database' && $method === 'GET') {
        (new HealthController())->checkDatabase();
    }

    // Auth routes
    if ($path === '/auth/register' && $method === 'POST') {
        (new AuthController())->register();
    } elseif ($path === '/auth/login' && $method === 'POST') {
        (new AuthController())->login();
    } elseif ($path === '/auth/profile' && $method === 'PUT') {
        (new AuthController())->updateProfile();
    }

    // Todo routes
    elseif ($path === '/todos' && $method === 'POST') {
        (new TodoController())->create();
    } elseif ($path === '/todos' && $method === 'GET') {
        (new TodoController())->getAll();
    } elseif (preg_match('#^/todos/(\d+)$#', $path, $matches) && $method === 'GET') {
        (new TodoController())->getById((int)$matches[1]);
    } elseif (preg_match('#^/todos/(\d+)$#', $path, $matches) && $method === 'PUT') {
        (new TodoController())->update((int)$matches[1]);
    } elseif (preg_match('#^/todos/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        (new TodoController())->delete((int)$matches[1]);
    }

    // 404 Not Found
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}
