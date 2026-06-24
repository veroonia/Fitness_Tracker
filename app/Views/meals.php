<?php

$goalLabels = [
    'maintain' => 'Maintenance',
    'loss_mild' => 'Mild Deficit',
    'loss' => 'Moderate Deficit',
    'loss_extreme' => 'Huge Deficit',
    'gain_mild' => 'Lean Gain',
    'gain' => 'Aggressive Gain',
    'gain_fast' => 'Aggressive Gain',
    'deficit' => 'Moderate Deficit',
];

$goalKey = (string)($currentUser['goal_preference'] ?? '');
$goalText = $goalLabels[$goalKey] ?? (($goalKey !== '') ? ucfirst(str_replace('_', ' ', $goalKey)) : 'Not set');
$fullName = trim((string)($currentUser['username'] ?? 'User'));
$firstName = $fullName !== '' ? explode(' ', $fullName)[0] : 'User';
$displayName = ucfirst(strtolower($firstName));

$mealPrepSuggestions = [
    'maintain' => [
        ['title' => 'Greek yogurt bowl', 'time' => 'Breakfast', 'kcal' => '420 kcal', 'focus' => 'Protein + slow carbs', 'image' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Chicken quinoa plate', 'time' => 'Lunch', 'kcal' => '560 kcal', 'focus' => 'Balanced macros', 'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Salmon rice bowl', 'time' => 'Dinner', 'kcal' => '610 kcal', 'focus' => 'Omega-3 + fiber', 'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80'],
    ],
    'loss_mild' => [
        ['title' => 'Egg white wrap', 'time' => 'Breakfast', 'kcal' => '310 kcal', 'focus' => 'Lean protein', 'image' => 'https://images.unsplash.com/photo-1495214783159-3503fd1b572d?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Turkey salad jar', 'time' => 'Lunch', 'kcal' => '430 kcal', 'focus' => 'High volume', 'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Tuna veggie skillet', 'time' => 'Dinner', 'kcal' => '480 kcal', 'focus' => 'Low calorie density', 'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80'],
    ],
    'loss' => [
        ['title' => 'Overnight oats', 'time' => 'Breakfast', 'kcal' => '350 kcal', 'focus' => 'Fiber + satiety', 'image' => 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Chicken rice box', 'time' => 'Lunch', 'kcal' => '500 kcal', 'focus' => 'Controlled carbs', 'image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Lean beef salad', 'time' => 'Dinner', 'kcal' => '540 kcal', 'focus' => 'High protein', 'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80'],
    ],
    'loss_extreme' => [
        ['title' => 'Protein smoothie', 'time' => 'Breakfast', 'kcal' => '240 kcal', 'focus' => 'Quick, light start', 'image' => 'https://images.unsplash.com/photo-1502741224143-90386d7f8c82?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Chicken broccoli box', 'time' => 'Lunch', 'kcal' => '360 kcal', 'focus' => 'Very lean meal prep', 'image' => 'https://images.unsplash.com/photo-1513442542250-854d436a73f2?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'White fish tray', 'time' => 'Dinner', 'kcal' => '390 kcal', 'focus' => 'High protein / low fat', 'image' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?auto=format&fit=crop&w=800&q=80'],
    ],
    'gain_mild' => [
        ['title' => 'Peanut butter oats', 'time' => 'Breakfast', 'kcal' => '520 kcal', 'focus' => 'Dense breakfast calories', 'image' => 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Chicken pasta bowl', 'time' => 'Lunch', 'kcal' => '680 kcal', 'focus' => 'Carb support', 'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Salmon avocado plate', 'time' => 'Dinner', 'kcal' => '710 kcal', 'focus' => 'Healthy fats', 'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80'],
    ],
    'gain' => [
        ['title' => 'Egg toast stack', 'time' => 'Breakfast', 'kcal' => '590 kcal', 'focus' => 'Protein + carbs', 'image' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Rice chicken bowl', 'time' => 'Lunch', 'kcal' => '770 kcal', 'focus' => 'Muscle gain prep', 'image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Beef potato tray', 'time' => 'Dinner', 'kcal' => '820 kcal', 'focus' => 'Calorie surplus', 'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80'],
    ],
    'gain_fast' => [
        ['title' => 'Mass gainer oats', 'time' => 'Breakfast', 'kcal' => '720 kcal', 'focus' => 'Big breakfast prep', 'image' => 'https://images.unsplash.com/photo-1502741224143-90386d7f8c82?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Double chicken pasta', 'time' => 'Lunch', 'kcal' => '920 kcal', 'focus' => 'High carb + protein', 'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?auto=format&fit=crop&w=800&q=80'],
        ['title' => 'Rice beef power bowl', 'time' => 'Dinner', 'kcal' => '980 kcal', 'focus' => 'Heavy surplus meal', 'image' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=800&q=80'],
    ],
];

$suggestions = $mealPrepSuggestions[$goalKey] ?? $mealPrepSuggestions['maintain'];
$currentCalories = (float)($currentUser['calories_today'] ?? 0);
$dailyGoalCalories = isset($currentUser['daily_goal_calories']) ? (int)$currentUser['daily_goal_calories'] : null;
$calorieSummary = $dailyGoalCalories !== null
    ? number_format($currentCalories, 0) . ' / ' . number_format((float)$dailyGoalCalories, 0) . ' kcal'
    : number_format($currentCalories, 0) . ' kcal';
?>

<main class="meal-board-shell">
    <aside class="meal-board-sidebar">
        <div class="dash-logo">FT</div>
        <ul class="dash-nav-list">
            <li><a href="index.php?route=dashboard">Home</a></li>
            <li class="active"><a href="index.php?route=meals">Meals</a></li>
            <li><a href="index.php?route=dashboard/stats">Stats</a></li>
            <li><a href="index.php?route=profile">Profile</a></li>
        </ul>
    </aside>

    <section class="meal-board-content">
        <header class="meal-board-topbar">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr((string)($currentUser['username'] ?? 'U'), 0, 1)); ?></div>
                <div>
                    <h2><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p>Meal prep suggestions for <?php echo htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="dash-top-actions">
                <a class="btn-ghost dashboard-exit" href="index.php?route=dashboard">Back to Dashboard</a>
                <a class="btn-ghost dashboard-exit" href="index.php?route=home">Calculator</a>
                <button id="mealsLogoutBtn" class="btn-ghost" type="button">Log Out</button>
            </div>
        </header>

        <section class="meal-board-hero">
            <div class="meal-board-copy">
                <p class="eyebrow">Meal Prep Suggestions</p>
                <h3>Plan your week around <?php echo htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8'); ?></h3>
                <p>Use these meal-prep ideas as a starting point, then adjust portions to match your calorie target and training day.</p>
            </div>
            <div class="meal-board-stats">
                <div class="meal-stat">
                    <span>Daily target</span>
                    <strong><?php echo htmlspecialchars($calorieSummary, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="meal-stat">
                    <span>Goal</span>
                    <strong><?php echo htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="meal-stat">
                    <span>Protein</span>
                    <strong><?php echo htmlspecialchars((string)($currentUser['weight_kg'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?> kg</strong>
                </div>
                <div class="meal-stat">
                    <span>Focus</span>
                    <strong>Meal prep ready</strong>
                </div>
            </div>
        </section>

        <section class="meal-prep-panel">
            <div class="meal-prep-toolbar">
                <div>
                    <h3>Suggested meal preps</h3>
                    <p>Pre-built ideas for breakfast, lunch, and dinner that fit your goal.</p>
                </div>
                <div class="meal-pill-group">
                    <span class="meal-pill active"><?php echo htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="meal-pill"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>

            <div class="meal-prep-grid">
                <?php foreach ($suggestions as $index => $suggestion): ?>
                    <article class="meal-prep-card">
                        <div class="meal-prep-image">
                            <img src="<?php echo htmlspecialchars($suggestion['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($suggestion['title'], ENT_QUOTES, 'UTF-8'); ?>" />
                            <button class="meal-prep-add" type="button" aria-label="Save suggestion <?php echo (int)($index + 1); ?>">+</button>
                        </div>
                        <div class="meal-prep-body">
                            <p class="meal-prep-time"><?php echo htmlspecialchars($suggestion['time'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <h4><?php echo htmlspecialchars($suggestion['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="meal-prep-focus"><?php echo htmlspecialchars($suggestion['focus'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="meal-prep-meta">
                                <span><?php echo htmlspecialchars($suggestion['kcal'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>Meal prep</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </section>
</main>
