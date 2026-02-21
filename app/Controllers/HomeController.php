<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $currentUser = null;
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $currentUser = [
                'username' => $_SESSION['user']['username'] ?? null,
                'email' => $_SESSION['user']['email'] ?? null,
            ];
        }

        $title = 'FitTrack Studio';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/home/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
