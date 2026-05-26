<?php

declare(strict_types=1);

class Meal
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $foodQuery, float $calories, float $proteinG, float $carbsG, float $fatG): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO meals (user_id, food_query, calories, protein_g, carbs_g, fat_g) VALUES (:user_id, :food_query, :calories, :protein_g, :carbs_g, :fat_g)'
        );

        return $statement->execute([
            'user_id' => $userId,
            'food_query' => $foodQuery,
            'calories' => $calories,
            'protein_g' => $proteinG,
            'carbs_g' => $carbsG,
            'fat_g' => $fatG,
        ]);
    }

    public function latestByUser(int $userId, int $limit = 8): array
    {
        $statement = $this->db->prepare(
            'SELECT id, food_query, calories, protein_g, carbs_g, fat_g, created_at
             FROM meals
             WHERE user_id = :user_id
             ORDER BY id DESC
             LIMIT :row_limit'
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':row_limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    public function totalsByUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT
                COALESCE(SUM(calories), 0) AS calories,
                COALESCE(SUM(protein_g), 0) AS protein_g,
                COALESCE(SUM(carbs_g), 0) AS carbs_g,
                COALESCE(SUM(fat_g), 0) AS fat_g
             FROM meals
             WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        $totals = $statement->fetch();

        return [
            'calories' => round((float)($totals['calories'] ?? 0), 1),
            'protein_g' => round((float)($totals['protein_g'] ?? 0), 1),
            'carbs_g' => round((float)($totals['carbs_g'] ?? 0), 1),
            'fat_g' => round((float)($totals['fat_g'] ?? 0), 1),
        ];
    }

    public function totalsByUserForDate(int $userId, ?string $date = null): array
    {
        // If no date provided, use today's date
        $date = $date ?: date('Y-m-d');

        $statement = $this->db->prepare(
            'SELECT
                COALESCE(SUM(calories), 0) AS calories,
                COALESCE(SUM(protein_g), 0) AS protein_g,
                COALESCE(SUM(carbs_g), 0) AS carbs_g,
                COALESCE(SUM(fat_g), 0) AS fat_g
             FROM meals
             WHERE user_id = :user_id
             AND DATE(created_at) = :target_date'
        );
        $statement->execute([
            'user_id' => $userId,
            'target_date' => $date,
        ]);

        $totals = $statement->fetch();

        return [
            'calories' => round((float)($totals['calories'] ?? 0), 1),
            'protein_g' => round((float)($totals['protein_g'] ?? 0), 1),
            'carbs_g' => round((float)($totals['carbs_g'] ?? 0), 1),
            'fat_g' => round((float)($totals['fat_g'] ?? 0), 1),
        ];
    }

    public function calorieTotalsByUserForMonth(int $userId, int $year, int $month): array
    {
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $statement = $this->db->prepare(
            'SELECT
                DATE(created_at) AS meal_date,
                COALESCE(SUM(calories), 0) AS calories
             FROM meals
             WHERE user_id = :user_id
             AND DATE(created_at) BETWEEN :month_start AND :month_end
             GROUP BY DATE(created_at)
             ORDER BY meal_date ASC'
        );
        $statement->execute([
            'user_id' => $userId,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
        ]);

        $totals = [];
        foreach ($statement->fetchAll() ?: [] as $row) {
            $totals[(string)$row['meal_date']] = round((float)$row['calories'], 1);
        }

        return $totals;
    }

    public function deleteByUser(int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM meals WHERE user_id = :user_id');

        return $statement->execute(['user_id' => $userId]);
    }

    public function deleteById(int $mealId, int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM meals WHERE id = :id AND user_id = :user_id');
        return $statement->execute(['id' => $mealId, 'user_id' => $userId]);
    }
}
