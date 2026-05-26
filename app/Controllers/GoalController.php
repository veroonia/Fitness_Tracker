<?php

declare(strict_types=1);

class GoalController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function show(): void
    {
        $this->ensureAuthenticated();

        if (($this->sessionUser()['goal_preference'] ?? null) !== null) {
            header('Location: index.php?route=dashboard');
            exit;
        }

        $currentUser = $this->sessionUser();
        $title = 'Pick Your Goal - FitTrack Studio';
        $scriptFile = 'public/assets/js/goal.js';
        $bodyClass = 'page-goals';
        $extraStyleFile = 'public/assets/css/goal.css';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/select.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function save(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $goal = strtolower(trim((string)($_POST['goal'] ?? '')));
        if (!in_array($goal, ['deficit', 'gain'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Please select either deficit or gain.']);
            return;
        }

        $userId = (int)$this->sessionUser()['id'];
        $this->savePostedProfileMetrics($userId);

        $updated = $this->users->updateGoalPreference($userId, $goal);
        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to save goal preference.']);
            return;
        }

        $_SESSION['user']['goal_preference'] = $goal;
        $freshUser = $this->users->findById($userId);
        if ($freshUser !== null) {
            $_SESSION['user'] = [
                'id' => (int)$freshUser['id'],
                'username' => $freshUser['username'],
                'email' => $freshUser['email'],
                'goal_preference' => $freshUser['goal_preference'] ?? $goal,
                'age' => $freshUser['age'] ?? null,
                'height_cm' => $freshUser['height_cm'] ?? null,
                'weight_kg' => $freshUser['weight_kg'] ?? null,
                'bmi' => $freshUser['bmi'] ?? null,
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Goal saved.',
            'redirectTo' => 'index.php?route=dashboard'
        ]);
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
            $metrics['age']
        );
    }

    private function postedProfileMetrics(): ?array
    {
        $ageRaw = trim((string)($_POST['age'] ?? ''));
        $heightRaw = trim((string)($_POST['height_cm'] ?? ''));
        $weightRaw = trim((string)($_POST['weight_kg'] ?? ''));

        if ($ageRaw === '' || $heightRaw === '' || $weightRaw === '') {
            return null;
        }

        if (!is_numeric($ageRaw) || !is_numeric($heightRaw) || !is_numeric($weightRaw)) {
            return null;
        }

        $age = (int)$ageRaw;
        $heightCm = (float)$heightRaw;
        $weightKg = (float)$weightRaw;

        if ($age < 10 || $age > 100 || $heightCm < 100 || $heightCm > 260 || $weightKg < 20 || $weightKg > 400) {
            return null;
        }

        $heightM = $heightCm / 100;

        return [
            'age' => $age,
            'height_cm' => $heightCm,
            'weight_kg' => $weightKg,
            'bmi' => round($weightKg / ($heightM * $heightM), 2),
        ];
    }
}
