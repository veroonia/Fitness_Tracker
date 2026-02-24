<?php

declare(strict_types=1);

class NutritionService
{
    public function analyzeFood(string $query): array
    {
        $apiKey = AppConfig::nutritionApiKey();

        if ($apiKey === 'YOUR_USDA_API_KEY') {
            throw new RuntimeException('Nutrition API key is not configured. Create a .env file with USDA_API_KEY.');
        }

        $url = 'https://api.nal.usda.gov/fdc/v1/foods/search?api_key=' . urlencode($apiKey);
        $requestBody = json_encode([
            'query' => $query,
            'pageSize' => 1,
        ]);

        if ($requestBody === false) {
            throw new RuntimeException('Unable to prepare nutrition request.');
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $requestBody,
                'timeout' => 12,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new RuntimeException('Unable to contact nutrition API.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid nutrition API response.');
        }

        $foods = $data['foods'] ?? [];
        if (!is_array($foods) || !isset($foods[0]) || !is_array($foods[0])) {
            throw new RuntimeException('No nutrition results found for that food.');
        }

        $nutrients = $foods[0]['foodNutrients'] ?? [];
        if (!is_array($nutrients)) {
            $nutrients = [];
        }

        $calories = $this->findCalories($nutrients);
        $protein = $this->findNutrientValue($nutrients, ['1003', '203'], ['Protein']);
        $carbs = $this->findNutrientValue($nutrients, ['1005', '205'], ['Carbohydrate']);
        $fat = $this->findNutrientValue($nutrients, ['1004', '204'], ['Total lipid', 'Fat']);

        return [
            'calories' => round($calories, 1),
            'protein_g' => round($protein, 1),
            'carbs_g' => round($carbs, 1),
            'fat_g' => round($fat, 1),
        ];
    }

    private function findCalories(array $nutrients): float
    {
        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient) || !isset($nutrient['value'])) {
                continue;
            }

            $number = isset($nutrient['nutrientNumber']) ? (string)$nutrient['nutrientNumber'] : '';
            $unit = isset($nutrient['unitName']) ? strtoupper((string)$nutrient['unitName']) : '';

            if (($number === '1008' || $number === '208') && $unit === 'KCAL') {
                return (float)$nutrient['value'];
            }
        }

        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient) || !isset($nutrient['value'])) {
                continue;
            }

            $name = isset($nutrient['nutrientName']) ? strtolower((string)$nutrient['nutrientName']) : '';
            $unit = isset($nutrient['unitName']) ? strtoupper((string)$nutrient['unitName']) : '';

            if ($name !== '' && str_contains($name, 'energy') && $unit === 'KCAL') {
                return (float)$nutrient['value'];
            }
        }

        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient) || !isset($nutrient['value'])) {
                continue;
            }

            $name = isset($nutrient['nutrientName']) ? strtolower((string)$nutrient['nutrientName']) : '';
            $unit = isset($nutrient['unitName']) ? strtoupper((string)$nutrient['unitName']) : '';

            if ($name !== '' && str_contains($name, 'energy') && ($unit === 'KJ' || $unit === 'KILOJOULE')) {
                return (float)$nutrient['value'] / 4.184;
            }
        }

        return 0.0;
    }

    private function findNutrientValue(array $nutrients, array $targetNumbers, array $fallbackNames): float
    {
        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient) || !isset($nutrient['value'])) {
                continue;
            }

            $number = isset($nutrient['nutrientNumber']) ? (string)$nutrient['nutrientNumber'] : '';
            if ($number !== '' && in_array($number, $targetNumbers, true)) {
                return (float)$nutrient['value'];
            }
        }

        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient) || !isset($nutrient['value'])) {
                continue;
            }

            $name = isset($nutrient['nutrientName']) ? strtolower((string)$nutrient['nutrientName']) : '';
            if ($name === '') {
                continue;
            }

            foreach ($fallbackNames as $fallbackName) {
                if (str_contains($name, strtolower($fallbackName))) {
                    return (float)$nutrient['value'];
                }
            }
        }

        return 0.0;
    }
}
