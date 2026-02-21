<?php

declare(strict_types=1);

class NutritionService
{
    public function analyzeFood(string $query): array
    {
        $appId = AppConfig::nutritionAppId();
        $appKey = AppConfig::nutritionAppKey();

        if ($appId === 'YOUR_EDAMAM_APP_ID' || $appKey === 'YOUR_EDAMAM_APP_KEY') {
            throw new RuntimeException('Nutrition API key is not configured. Create a .env file with EDAMAM_APP_ID and EDAMAM_APP_KEY.');
        }

        $url = 'https://api.edamam.com/api/nutrition-data?app_id=' . urlencode($appId)
            . '&app_key=' . urlencode($appKey)
            . '&nutrition-type=logging&ingr=' . urlencode($query);

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new RuntimeException('Unable to contact nutrition API.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid nutrition API response.');
        }

        $calories = isset($data['calories']) ? (float)$data['calories'] : 0.0;
        $nutrients = $data['totalNutrients'] ?? [];

        return [
            'calories' => round($calories, 1),
            'protein_g' => isset($nutrients['PROCNT']['quantity']) ? round((float)$nutrients['PROCNT']['quantity'], 1) : 0.0,
            'carbs_g' => isset($nutrients['CHOCDF']['quantity']) ? round((float)$nutrients['CHOCDF']['quantity'], 1) : 0.0,
            'fat_g' => isset($nutrients['FAT']['quantity']) ? round((float)$nutrients['FAT']['quantity'], 1) : 0.0,
        ];
    }
}
