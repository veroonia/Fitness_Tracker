<?php

declare(strict_types=1);

class NutritionService
{
    // Common foods database with nutritional info per 100g
    private const COMMON_FOODS = [
        'chicken breast' => ['calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
        'chicken' => ['calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
        'grilled chicken' => ['calories' => 165, 'protein' => 31, 'carbs' => 0, 'fat' => 3.6],
        'turkey breast' => ['calories' => 189, 'protein' => 29, 'carbs' => 0, 'fat' => 7.4],
        'beef' => ['calories' => 250, 'protein' => 26, 'carbs' => 0, 'fat' => 15],
        'salmon' => ['calories' => 208, 'protein' => 20, 'carbs' => 0, 'fat' => 13],
        'tuna' => ['calories' => 132, 'protein' => 29, 'carbs' => 0, 'fat' => 1.3],
        'egg' => ['calories' => 155, 'protein' => 13, 'carbs' => 1.1, 'fat' => 11],
        'rice' => ['calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3],
        'pasta' => ['calories' => 371, 'protein' => 13, 'carbs' => 75, 'fat' => 1.1],
        'broccoli' => ['calories' => 34, 'protein' => 2.8, 'carbs' => 7, 'fat' => 0.4],
        'banana' => ['calories' => 89, 'protein' => 1.1, 'carbs' => 23, 'fat' => 0.3],
        'apple' => ['calories' => 52, 'protein' => 0.3, 'carbs' => 14, 'fat' => 0.2],
        'oats' => ['calories' => 389, 'protein' => 17, 'carbs' => 66, 'fat' => 6.9],
        'almonds' => ['calories' => 579, 'protein' => 21, 'carbs' => 22, 'fat' => 50],
        'peanut butter' => ['calories' => 588, 'protein' => 25, 'carbs' => 20, 'fat' => 50],
        'milk' => ['calories' => 61, 'protein' => 3.2, 'carbs' => 4.8, 'fat' => 3.3],
        'yogurt' => ['calories' => 59, 'protein' => 10, 'carbs' => 3.6, 'fat' => 0.4],
        'cheese' => ['calories' => 402, 'protein' => 25, 'carbs' => 1.3, 'fat' => 33],
        'sweet potato' => ['calories' => 86, 'protein' => 1.6, 'carbs' => 20, 'fat' => 0.1],
        'peas' => ['calories' => 81, 'protein' => 5.4, 'carbs' => 14, 'fat' => 0.4],
        'spinach' => ['calories' => 23, 'protein' => 2.9, 'carbs' => 3.6, 'fat' => 0.4],
    ];

    public function analyzeFood(string $query): array
    {
        // Extract weight from query (e.g., "grilled chicken 150g" -> 150)
        $weight = $this->extractWeight($query);
        $foodName = $this->cleanFoodName($query);

        // Try fallback database first (faster and more reliable)
        $nutrition = $this->findInCommonFoods($foodName);
        
        if ($nutrition !== null) {
            // Scale to actual weight (database values are per 100g)
            return [
                'calories' => round(($nutrition['calories'] * $weight) / 100, 1),
                'protein_g' => round(($nutrition['protein'] * $weight) / 100, 1),
                'carbs_g' => round(($nutrition['carbs'] * $weight) / 100, 1),
                'fat_g' => round(($nutrition['fat'] * $weight) / 100, 1),
            ];
        }

        // Fall back to USDA API if not in common foods
        return $this->analyzeViaUSDA($query);
    }

    private function extractWeight(string $query): float
    {
        // Extract weight like "150g", "150", "150 g"
        if (preg_match('/(\d+)\s*g\b/i', $query, $matches)) {
            return (float)$matches[1];
        }
        // Default to 100g if no weight specified
        return 100.0;
    }

    private function cleanFoodName(string $query): string
    {
        // Remove weight from query
        $cleaned = preg_replace('/\d+\s*g\b/i', '', $query);
        // Remove extra whitespace
        $cleaned = strtolower(trim($cleaned));
        return $cleaned;
    }

    private function findInCommonFoods(string $foodName): ?array
    {
        foreach (self::COMMON_FOODS as $commonName => $nutrition) {
            if (str_contains($foodName, $commonName) || str_contains($commonName, $foodName)) {
                return $nutrition;
            }
        }
        return null;
    }

    private function analyzeViaUSDA(string $query): array
    {
        $apiKey = AppConfig::nutritionApiKey();

        if ($apiKey === 'YOUR_USDA_API_KEY') {
            throw new RuntimeException('Nutrition API key is not configured. Create a .env file with USDA_API_KEY.');
        }

        // Step 1: Search for food to get FDC ID
        $searchUrl = 'https://api.nal.usda.gov/fdc/v1/foods/search?api_key=' . urlencode($apiKey);
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

        $searchResponse = @file_get_contents($searchUrl, false, $context);
        if ($searchResponse === false) {
            throw new RuntimeException('Unable to contact nutrition API.');
        }

        $searchData = json_decode($searchResponse, true);
        if (!is_array($searchData)) {
            throw new RuntimeException('Invalid nutrition API response.');
        }

        $foods = $searchData['foods'] ?? [];
        if (!is_array($foods) || !isset($foods[0]) || !is_array($foods[0])) {
            throw new RuntimeException('No nutrition results found for that food.');
        }

        $fdcId = $foods[0]['fdcId'] ?? null;
        if ($fdcId === null) {
            throw new RuntimeException('Unable to identify food from API response.');
        }

        // Step 2: Fetch detailed nutrition data using FDC ID
        $detailUrl = 'https://api.nal.usda.gov/fdc/v1/food/' . urlencode((string)$fdcId) . '?api_key=' . urlencode($apiKey);
        
        $detailContext = stream_context_create([
            'http' => [
                'timeout' => 12,
            ],
        ]);

        $detailResponse = @file_get_contents($detailUrl, false, $detailContext);
        if ($detailResponse === false) {
            throw new RuntimeException('Unable to fetch detailed nutrition data.');
        }

        $detailData = json_decode($detailResponse, true);
        if (!is_array($detailData)) {
            throw new RuntimeException('Invalid detailed nutrition API response.');
        }

        $nutrients = $detailData['foodNutrients'] ?? [];
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
