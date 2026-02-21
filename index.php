<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/app/Config/Database.php';
require_once __DIR__ . '/app/Config/AppConfig.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Meal.php';
require_once __DIR__ . '/app/Services/NutritionService.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/GoalController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$homeController = new HomeController();
$authController = new AuthController();
$goalController = new GoalController();
$dashboardController = new DashboardController();

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

if ($route === 'goals' && $method === 'GET') {
    $goalController->show();
    exit;
}

if ($route === 'goals/save' && $method === 'POST') {
    $goalController->save();
    exit;
}

if ($route === 'dashboard' && $method === 'GET') {
    $dashboardController->index();
    exit;
}

if ($route === 'dashboard/log-food' && $method === 'POST') {
    $dashboardController->logFood();
    exit;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found.'
]);
