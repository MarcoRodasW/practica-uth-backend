<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use UthApi\Utils\JWT;
use UthApi\Utils\Response;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize JWT
JWT::init();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error handling
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($exception) {
    Response::error('Internal server error', 500);
});

try {
    require_once __DIR__ . '/../src/Routes/api.php';
    handleRoutes();
} catch (Exception $e) {
    Response::error('Internal server error', 500);
}
