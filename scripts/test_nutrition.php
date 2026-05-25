<?php
require __DIR__ . '/../app/Services/NutritionService.php';
$s = new NutritionService();
$result = $s->analyzeFood('tiramisu 200g');
print_r($result);
