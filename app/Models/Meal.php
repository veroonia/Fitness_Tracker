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
}
