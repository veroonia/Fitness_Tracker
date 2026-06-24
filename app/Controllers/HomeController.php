<?php

declare(strict_types=1);

class HomeController
{
    private const GOAL_KCAL_ADJUSTMENTS = [
        'maintain' => 0,
        'loss_mild' => -275,
        'loss' => -550,
        'loss_extreme' => -1100,
        'gain_mild' => 275,
        'gain' => 550,
        'gain_fast' => 1100,
        // Backward compatibility
        'deficit' => -550,
    ];

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

        // If user is logged in, attempt to compute a daily calorie goal and today's intake
        if (is_array($currentUser) && !empty($currentUser['id'])) {
            try {
                $mealModel = new Meal();

                $userSession = $_SESSION['user'];
                $age = isset($userSession['age']) ? (int)$userSession['age'] : null;
                $heightCm = isset($userSession['height_cm']) ? (float)$userSession['height_cm'] : null;
                $weightKg = isset($userSession['weight_kg']) ? (float)$userSession['weight_kg'] : null;
                $sex = isset($userSession['sex']) ? strtolower((string)$userSession['sex']) : null;
                $activityFactor = isset($userSession['activity_factor']) ? (float)$userSession['activity_factor'] : null;
                $goal = $userSession['goal_preference'] ?? null;

                if ($age !== null && $heightCm !== null && $weightKg !== null && $goal !== null) {
                    $maintenance = $this->calculateMaintenanceCalories($age, $heightCm, $weightKg, $sex, $activityFactor);

                    $adjustment = self::GOAL_KCAL_ADJUSTMENTS[$goal] ?? self::GOAL_KCAL_ADJUSTMENTS['maintain'];
                    $target = (int)(round(($maintenance + $adjustment) / 10) * 10);

                    $currentUser['calorie_goal'] = $target;
                    $todayTotals = $mealModel->totalsByUserForDate((int)$currentUser['id']);
                    $currentUser['calories_today'] = $todayTotals['calories'] ?? 0.0;
                }
            } catch (Throwable $e) {
                // ignore failures computing goals; don't break home page
            }
        }

        $title = 'FitTrack Studio';
        $scriptFile = 'public/assets/js/app.js';
        $bodyClass = 'page-home';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/home.php';
        require __DIR__ . '/../Views/layouts/footer.php';
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
