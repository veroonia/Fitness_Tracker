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

        if (($user['goal_preference'] ?? null) === null) {
            header('Location: index.php?route=goals');
            exit;
        }

        $currentUser = $user;
        $totals = $this->meals->totalsByUser((int)$user['id']);
        $recentMeals = $this->meals->latestByUser((int)$user['id']);

        $title = 'Dashboard - FitTrack Studio';
        $scriptFile = 'public/assets/js/dashboard.js';
        $bodyClass = 'page-dashboard';

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/dashboard/index.php';
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

        $totals = $this->meals->totalsByUser((int)$user['id']);

        echo json_encode([
            'success' => true,
            'message' => 'Meal logged successfully.',
            'entry' => [
                'food_query' => $foodText,
                'calories' => $analysis['calories'],
                'protein_g' => $analysis['protein_g'],
                'carbs_g' => $analysis['carbs_g'],
                'fat_g' => $analysis['fat_g'],
            ],
            'totals' => $totals,
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
