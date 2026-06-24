<?php

declare(strict_types=1);

class MealsController
{
    private User $users;
    private Meal $meals;
    private const GOAL_KCAL_MULTIPLIERS = [
        'maintain' => 1.00,
        'loss_mild' => 0.90,
        'loss' => 0.80,
        'loss_extreme' => 0.70,
        'gain_mild' => 1.10,
        'gain' => 1.15,
        'gain_fast' => 1.15,
        'deficit' => 0.80,
    ];

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

        $dailyGoalCalories = $this->calculateDailyGoalCalories($currentUser);
        if ($dailyGoalCalories !== null) {
            $currentUser['daily_goal_calories'] = $dailyGoalCalories;
        }

        $totals = $this->meals->totalsByUserForDate((int)$currentUser['id']);
        $currentUser['calories_today'] = $totals['calories'] ?? 0.0;

        $title = 'Meals - FitTrack Studio';
        $scriptFile = 'public/assets/js/meals.js';
        $bodyClass = 'page-meals';
        $extraStyleFiles = ['public/assets/css/meals.css'];

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/meals.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    private function ensureAuthenticated(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: index.php?route=home');
            exit;
        }
    }

    private function fetchCurrentUser(): ?array
    {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
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
            'sex' => $user['sex'] ?? null,
            'activity_factor' => $user['activity_factor'] ?? null,
            'age' => $user['age'] ?? null,
            'height_cm' => $user['height_cm'] ?? null,
            'weight_kg' => $user['weight_kg'] ?? null,
            'bmi' => $user['bmi'] ?? null,
        ];

        return $_SESSION['user'];
    }

    private function calculateDailyGoalCalories(array $user): ?int
    {
        $age = isset($user['age']) ? (int)$user['age'] : null;
        $heightCm = isset($user['height_cm']) ? (float)$user['height_cm'] : null;
        $weightKg = isset($user['weight_kg']) ? (float)$user['weight_kg'] : null;
        $sex = isset($user['sex']) ? strtolower((string)$user['sex']) : null;
        $activityFactor = isset($user['activity_factor']) ? (float)$user['activity_factor'] : null;
        $goal = $user['goal_preference'] ?? null;

        if ($age === null || $heightCm === null || $weightKg === null || $goal === null) {
            return null;
        }

        $maintenance = $this->calculateMaintenanceCalories($age, $heightCm, $weightKg, $sex, $activityFactor);
        $multiplier = self::GOAL_KCAL_MULTIPLIERS[$goal] ?? self::GOAL_KCAL_MULTIPLIERS['maintain'];

        return (int)(round(($maintenance * $multiplier) / 10) * 10);
    }

    private function calculateMaintenanceCalories(int $age, float $heightCm, float $weightKg, ?string $sex, ?float $activityFactor): int
    {
        $activity = $activityFactor !== null ? $activityFactor : 1.55;

        if ($sex === 'male') {
            $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5;
        } elseif ($sex === 'female') {
            $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161;
        } else {
            $bmrMale = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5;
            $bmrFemale = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161;
            $bmr = ($bmrMale + $bmrFemale) / 2.0;
        }

        return (int)(round(($bmr * $activity) / 10) * 10);
    }
}
