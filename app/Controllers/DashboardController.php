<?php

declare(strict_types=1);

class DashboardController
{
    private Meal $meals;
    private NutritionService $nutrition;

    public function __construct()
    {
        $this->meals = new Meal();
        $this->nutrition = new NutritionService();
    }

    public function index(): void
    {
        $this->ensureAuthenticated();
        $user = $this->sessionUser();
        $dailyGoalCalories = $this->calculateDailyGoalCalories($user);

        if ($dailyGoalCalories !== null) {
            $user['daily_goal_calories'] = $dailyGoalCalories;
        }

        if (($user['goal_preference'] ?? null) === null) {
            header('Location: index.php?route=goals');
            exit;
        }

        $currentUser = $user;
        $totals = $this->meals->totalsByUserForDate((int)$user['id']);
        $recentMeals = $this->meals->latestByUser((int)$user['id']);

        $title = 'Dashboard - FitTrack Studio';
        $scriptFile = 'public/assets/js/dashboard.js';
        $bodyClass = 'page-dashboard';
        $extraStyleFile = 'public/assets/css/dashboard.css';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/dashboard.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function logFood(): void
    {
        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $user = $this->sessionUser();
        $foodText = trim((string)($_POST['food_text'] ?? ''));

        if ($foodText === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Please enter a food description.']);
            return;
        }

        try {
            $analysis = $this->nutrition->analyzeFood($foodText);
        } catch (RuntimeException $error) {
            http_response_code(502);
            echo json_encode(['success' => false, 'message' => $error->getMessage()]);
            return;
        }

        $saved = $this->meals->create(
            (int)$user['id'],
            $foodText,
            (float)$analysis['calories'],
            (float)$analysis['protein_g'],
            (float)$analysis['carbs_g'],
            (float)$analysis['fat_g']
        );

        if (!$saved) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to save meal log.']);
            return;
        }
        // Fetch the latest entry we just inserted (ordered by id desc)
        $latest = $this->meals->latestByUser((int)$user['id'], 1);
        $entry = [];
        if (!empty($latest) && isset($latest[0])) {
            $entry = $latest[0];
        } else {
            $entry = [
                'id' => null,
                'food_query' => $foodText,
                'calories' => $analysis['calories'],
                'protein_g' => $analysis['protein_g'],
                'carbs_g' => $analysis['carbs_g'],
                'fat_g' => $analysis['fat_g'],
            ];
        }

        $totals = $this->meals->totalsByUserForDate((int)$user['id']);

        echo json_encode([
            'success' => true,
            'message' => 'Meal logged successfully.',
            'entry' => $entry,
            'totals' => $totals,
        ]);
    }

    public function deleteFood(): void
    {
        // Start output buffering to capture any stray output (warnings, notices,
        // or accidental whitespace) so we can log it and return clean JSON.
        if (!ob_get_level()) {
            ob_start();
        }

        header('Content-Type: application/json');
        $this->ensureAuthenticated(true);

        $user = $this->sessionUser();
        $mealId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($mealId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid meal id.']);
            return;
        }

        $deleted = $this->meals->deleteById($mealId, (int)$user['id']);
        if (!$deleted) {
            // Flush and capture any stray output before returning
            $extra = '';
            if (ob_get_level()) {
                $extra = trim((string)ob_get_clean());
            }
            if ($extra !== '') {
                $logFile = sys_get_temp_dir() . '/fitness_tracker_delete_food.log';
                file_put_contents($logFile, date('c') . " FAILED_DELETE_EXTRA_OUTPUT: " . $extra . PHP_EOL, FILE_APPEND);
            }

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to delete meal.']);
            exit;
        }

        $totals = $this->meals->totalsByUserForDate((int)$user['id']);

        // Capture and log any stray output that would break JSON on the client.
        $extra = '';
        if (ob_get_level()) {
            $extra = trim((string)ob_get_clean());
        }
        if ($extra !== '') {
            $logFile = sys_get_temp_dir() . '/fitness_tracker_delete_food.log';
            file_put_contents($logFile, date('c') . " EXTRA_OUTPUT: " . $extra . PHP_EOL, FILE_APPEND);
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Meal deleted.',
            'totals' => $totals,
        ]);
        exit;
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

    private function calculateDailyGoalCalories(array $user): ?int
    {
        $age = isset($user['age']) ? (int)$user['age'] : null;
        $heightCm = isset($user['height_cm']) ? (float)$user['height_cm'] : null;
        $weightKg = isset($user['weight_kg']) ? (float)$user['weight_kg'] : null;
        $goal = $user['goal_preference'] ?? null;

        if ($age === null || $heightCm === null || $weightKg === null || $goal === null) {
            return null;
        }

        $bmrMale = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + 5;
        $bmrFemale = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) - 161;
        $bmrAvg = ($bmrMale + $bmrFemale) / 2.0;

        $activityFactor = 1.55;
        $maintenance = (int)(round(($bmrAvg * $activityFactor) / 10) * 10);

        if ($goal === 'deficit') {
            return (int)(round(($maintenance * 0.80) / 10) * 10);
        }

        return (int)(round(($maintenance * 1.10) / 10) * 10);
    }
}
