<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $currentUser = null;
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $currentUser = [
                'id' => $_SESSION['user']['id'] ?? null,
                'username' => $_SESSION['user']['username'] ?? null,
                'email' => $_SESSION['user']['email'] ?? null,
                'goal_preference' => $_SESSION['user']['goal_preference'] ?? null,
            ];
        }

        $title = 'FitTrack Studio';
        $scriptFile = 'public/assets/js/app.js';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/home/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
