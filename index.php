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
require_once __DIR__ . '/app/Controllers/ProfileController.php';

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$homeController = new HomeController();
$authController = new AuthController();
$goalController = new GoalController();
$dashboardController = new DashboardController();
$profileController = new ProfileController();

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

if ($route === 'profile' && $method === 'GET') {
    $profileController->index();
    exit;
}

if ($route === 'profile/update-data' && $method === 'POST') {
    $profileController->updateData();
    exit;
}

if ($route === 'profile/settings' && $method === 'GET') {
    $profileController->settings();
    exit;
}

if ($route === 'profile/settings/add-account' && $method === 'POST') {
    $profileController->addAccount();
    exit;
}

if ($route === 'profile/settings/edit-account' && $method === 'POST') {
    $profileController->editAccount();
    exit;
}

if ($route === 'profile/settings/delete-account' && $method === 'POST') {
    $profileController->deleteAccount();
    exit;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Route not found.'
]);
