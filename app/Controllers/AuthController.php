<?php

declare(strict_types=1);

class AuthController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function signup(): void
    {
        header('Content-Type: application/json');

        $username = trim((string)($_POST['username'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $email === '' || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Provide valid username, email, and password (min 6 chars).']);
            return;
        }

        if ($this->users->findByEmail($email) !== null) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $createdUserId = $this->users->create($username, $email, $passwordHash);

        if ($createdUserId === null) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to create account right now.']);
            return;
        }

        $_SESSION['user'] = [
            'id' => $createdUserId,
            'username' => $username,
            'email' => $email,
            'goal_preference' => null,
            'age' => null,
            'height_cm' => null,
            'weight_kg' => null,
            'bmi' => null,
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully.',
            'user' => $_SESSION['user'],
            'redirectTo' => 'index.php?route=goals',
        ]);
    }

    public function login(): void
    {
        header('Content-Type: application/json');

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            return;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            return;
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'goal_preference' => $user['goal_preference'] ?? null,
            'age' => $user['age'] ?? null,
            'height_cm' => $user['height_cm'] ?? null,
            'weight_kg' => $user['weight_kg'] ?? null,
            'bmi' => $user['bmi'] ?? null,
        ];

        $redirectTo = ($_SESSION['user']['goal_preference'] ?? null) === null
            ? 'index.php?route=goals'
            : 'index.php?route=dashboard';

        echo json_encode([
            'success' => true,
            'message' => 'Logged in successfully.',
            'user' => $_SESSION['user'],
            'redirectTo' => $redirectTo,
        ]);
    }

    public function logout(): void
    {
        header('Content-Type: application/json');

        unset($_SESSION['user']);

        echo json_encode([
            'success' => true,
            'message' => 'Logged out.',
        ]);
    }
}
