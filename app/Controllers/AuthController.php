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

        $this->savePostedProfileMetrics($createdUserId);
        $createdUser = $this->users->findById($createdUserId);

        $_SESSION['user'] = [
            'id' => $createdUserId,
            'username' => $createdUser['username'] ?? $username,
            'email' => $createdUser['email'] ?? $email,
            'goal_preference' => $createdUser['goal_preference'] ?? null,
            'sex' => $createdUser['sex'] ?? null,
            'activity_factor' => $createdUser['activity_factor'] ?? null,
            'age' => $createdUser['age'] ?? null,
            'height_cm' => $createdUser['height_cm'] ?? null,
            'weight_kg' => $createdUser['weight_kg'] ?? null,
            'bmi' => $createdUser['bmi'] ?? null,
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

        $userId = (int)$user['id'];
        if ($this->savePostedProfileMetrics($userId)) {
            $freshUser = $this->users->findById($userId);
            if ($freshUser !== null) {
                $user = array_merge($user, $freshUser);
            }
        }

        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $user['username'],
            'email' => $user['email'],
            'goal_preference' => $user['goal_preference'] ?? null,
            'sex' => $user['sex'] ?? null,
            'activity_factor' => $user['activity_factor'] ?? null,
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

    private function savePostedProfileMetrics(int $userId): bool
    {
        $metrics = $this->postedProfileMetrics();
        if ($metrics === null) {
            return false;
        }

        return $this->users->updateProfileMetrics(
            $userId,
            $metrics['height_cm'],
            $metrics['weight_kg'],
            $metrics['bmi'],
            $metrics['age'],
            $metrics['sex'],
            $metrics['activity_factor']
        );
    }

    private function postedProfileMetrics(): ?array
    {
        $ageRaw = trim((string)($_POST['age'] ?? ''));
        $heightRaw = trim((string)($_POST['height_cm'] ?? ''));
        $weightRaw = trim((string)($_POST['weight_kg'] ?? ''));
        $sex = strtolower(trim((string)($_POST['sex'] ?? '')));
        $activityRaw = trim((string)($_POST['activity'] ?? ''));

        if ($ageRaw === '' || $heightRaw === '' || $weightRaw === '' || $sex === '' || $activityRaw === '') {
            return null;
        }

        if (!is_numeric($ageRaw) || !is_numeric($heightRaw) || !is_numeric($weightRaw) || !is_numeric($activityRaw)) {
            return null;
        }

        $age = (int)$ageRaw;
        $heightCm = (float)$heightRaw;
        $weightKg = (float)$weightRaw;
        $activityFactor = (float)$activityRaw;

        if ($age < 10 || $age > 100 || $heightCm < 100 || $heightCm > 260 || $weightKg < 20 || $weightKg > 400) {
            return null;
        }

        if (!in_array($sex, ['male', 'female'], true)) {
            return null;
        }

        if (!in_array($activityRaw, ['1.2', '1.375', '1.55', '1.725', '1.9'], true)) {
            return null;
        }

        $heightM = $heightCm / 100;

        return [
            'age' => $age,
            'height_cm' => $heightCm,
            'weight_kg' => $weightKg,
            'bmi' => round($weightKg / ($heightM * $heightM), 2),
            'sex' => $sex,
            'activity_factor' => $activityFactor,
        ];
    }
}
