<?php

declare(strict_types=1);

class ProfileController
{
    private User $users;
    private Meal $meals;

    public function __construct()
    {
        $this->users = new User();
        $this->meals = new Meal();
    }

    public function index(): void
    {
        $this->ensureAuthenticated();

        $currentUser = $this->fetchCurrentUser();
        if ($currentUser === null) {
            header('Location: index.php?route=home');
            exit;
        }

        $title = 'Profile - FitTrack Studio';
        $scriptFile = 'public/assets/js/profile.js';
        $bodyClass = 'page-profile';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/profile/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function settings(): void
    {
        $this->ensureAuthenticated();

        $currentUser = $this->fetchCurrentUser();
        if ($currentUser === null) {
            header('Location: index.php?route=home');
            exit;
        }

        $title = 'Profile Settings - FitTrack Studio';
        $scriptFile = 'public/assets/js/settings.js';
        $bodyClass = 'page-profile-settings';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/profile/settings.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function updateData(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $userId = (int)($this->sessionUser()['id'] ?? 0);
        $ageRaw = trim((string)($_POST['age'] ?? ''));
        $heightRaw = trim((string)($_POST['height_cm'] ?? ''));
        $weightRaw = trim((string)($_POST['weight_kg'] ?? ''));
        $goal = strtolower(trim((string)($_POST['goal_preference'] ?? '')));

        if (!in_array($goal, ['deficit', 'gain'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Please select a valid target goal.']);
            return;
        }

        $age = $ageRaw === '' ? null : (int)$ageRaw;
        $heightCm = $heightRaw === '' ? null : (float)$heightRaw;
        $weightKg = $weightRaw === '' ? null : (float)$weightRaw;
        $bmi = null;

        if ($age !== null && ($age < 10 || $age > 100)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Age must be between 10 and 100.']);
            return;
        }

        if ($heightCm !== null && ($heightCm < 100 || $heightCm > 260)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Height must be between 100 and 260 cm.']);
            return;
        }

        if ($weightKg !== null && ($weightKg < 20 || $weightKg > 400)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Weight must be between 20 and 400 kg.']);
            return;
        }

        if ($heightCm !== null && $weightKg !== null && $heightCm > 0) {
            $heightM = $heightCm / 100;
            $bmi = round($weightKg / ($heightM * $heightM), 2);
        }

        $updated = $this->users->updateProfileData($userId, $heightCm, $weightKg, $bmi, $age, $goal);
        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to update profile data.']);
            return;
        }

        $freshUser = $this->refreshSessionUser($userId);

        echo json_encode([
            'success' => true,
            'message' => 'Profile data updated.',
            'user' => $freshUser,
        ]);
    }

    public function addAccount(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

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

        echo json_encode([
            'success' => true,
            'message' => 'New account added successfully.',
        ]);
    }

    public function editAccount(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $userId = (int)($this->sessionUser()['id'] ?? 0);
        $username = trim((string)($_POST['username'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Provide valid username and email.']);
            return;
        }

        if ($this->users->emailExistsForOther($email, $userId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email is already used by another account.']);
            return;
        }

        $passwordHash = null;
        if ($password !== '') {
            if (strlen($password) < 6) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
                return;
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        $updated = $this->users->updateAccount($userId, $username, $email, $passwordHash);
        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to update account settings.']);
            return;
        }

        $freshUser = $this->refreshSessionUser($userId);

        echo json_encode([
            'success' => true,
            'message' => 'Account settings updated.',
            'user' => $freshUser,
        ]);
    }

    public function deleteAccount(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $userId = (int)($this->sessionUser()['id'] ?? 0);
        $confirmation = strtoupper(trim((string)($_POST['confirm_delete'] ?? '')));

        if ($confirmation !== 'DELETE') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Type DELETE to confirm account deletion.']);
            return;
        }

        $this->meals->deleteByUser($userId);
        $deleted = $this->users->deleteById($userId);

        if (!$deleted) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete account.']);
            return;
        }

        unset($_SESSION['user']);

        echo json_encode([
            'success' => true,
            'message' => 'Account deleted successfully.',
            'redirectTo' => 'index.php?route=home',
        ]);
    }

    private function fetchCurrentUser(): ?array
    {
        $userId = (int)($this->sessionUser()['id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            return null;
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

        return $_SESSION['user'];
    }

    private function refreshSessionUser(int $userId): ?array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            return null;
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

        return $_SESSION['user'];
    }

    private function ensureAuthenticated(bool $json = false): void
    {
        if (!isset($_SESSION['user']['id'])) {
            if ($json) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            } else {
                header('Location: index.php?route=home');
            }
            exit;
        }
    }

    private function sessionUser(): array
    {
        return is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : [];
    }
}
