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

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/goals/select.php';
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
        $updated = $this->users->updateGoalPreference($userId, $goal);
        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to save goal preference.']);
            return;
        }

        $_SESSION['user']['goal_preference'] = $goal;

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
}
