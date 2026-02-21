<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/app/Config/Database.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$homeController = new HomeController();
$authController = new AuthController();

if ($route === '' || $route === 'home') {
    $homeController->index();
    exit;
}

if ($route === 'auth/signup' && $method === 'POST') {
    $authController->signup();
    exit;
}

if ($route === 'auth/login' && $method === 'POST') {
    $authController->login();
    exit;
}

if ($route === 'auth/logout' && $method === 'POST') {
    $authController->logout();
    exit;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found.'
]);
