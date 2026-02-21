<main class="dashboard-shell">
    <aside class="dash-sidebar">
        <div class="dash-logo">FT</div>
        <ul class="dash-nav-list">
            <li class="active">Home</li>
            <li>Meals</li>
            <li>Stats</li>
            <li>Profile</li>
        </ul>
    </aside>

    <section class="dash-content">
        <header class="dash-topbar">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr((string)($currentUser['username'] ?? 'U'), 0, 1)); ?></div>
                <div>
                    <h2><?php echo htmlspecialchars((string)($currentUser['username'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p>Goal: <?php echo htmlspecialchars(ucfirst((string)($currentUser['goal_preference'] ?? 'Not set')), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="dash-top-actions">
                <a class="btn-ghost dashboard-exit" href="index.php?route=home">Back to Calculator</a>
                <button id="dashboardLogoutBtn" class="btn-ghost" type="button">Log Out</button>
            </div>
        </header>

        <div class="dash-grid">
            <section class="dash-hero">
                <h3>Daily Nutrition Overview</h3>
                <p>Log your food and we calculate macros using nutrition API.</p>
                <div class="hero-kcal"><span id="totalCaloriesValue"><?php echo htmlspecialchars((string)$totals['calories'], ENT_QUOTES, 'UTF-8'); ?></span> kcal</div>
                <div class="hero-macros">
                    <div><strong id="totalProteinValue"><?php echo htmlspecialchars((string)$totals['protein_g'], ENT_QUOTES, 'UTF-8'); ?>g</strong><small>Protein</small></div>
                    <div><strong id="totalCarbsValue"><?php echo htmlspecialchars((string)$totals['carbs_g'], ENT_QUOTES, 'UTF-8'); ?>g</strong><small>Carbs</small></div>
                    <div><strong id="totalFatValue"><?php echo htmlspecialchars((string)$totals['fat_g'], ENT_QUOTES, 'UTF-8'); ?>g</strong><small>Fat</small></div>
                </div>
            </section>

            <section class="dash-targets">
                <article class="target-card">
                    <h4>Calories</h4>
                    <p id="cardCaloriesValue"><?php echo htmlspecialchars((string)$totals['calories'], ENT_QUOTES, 'UTF-8'); ?> kcal</p>
                </article>
                <article class="target-card">
                    <h4>Protein</h4>
                    <p id="cardProteinValue"><?php echo htmlspecialchars((string)$totals['protein_g'], ENT_QUOTES, 'UTF-8'); ?> g</p>
                </article>
                <article class="target-card">
                    <h4>Carbs</h4>
                    <p id="cardCarbsValue"><?php echo htmlspecialchars((string)$totals['carbs_g'], ENT_QUOTES, 'UTF-8'); ?> g</p>
                </article>
                <article class="target-card">
                    <h4>Fat</h4>
                    <p id="cardFatValue"><?php echo htmlspecialchars((string)$totals['fat_g'], ENT_QUOTES, 'UTF-8'); ?> g</p>
                </article>
            </section>
        </div>

        <section class="food-log-panel">
            <h3>Log Food</h3>
            <p>Example: "2 eggs and 1 slice toast" or "150g grilled chicken"</p>
            <form id="foodLogForm" class="food-log-form" novalidate>
                <input id="foodText" type="text" placeholder="Describe your meal..." required />
                <button type="submit" class="btn-solid">Analyze &amp; Save</button>
            </form>
            <p id="foodLogError" class="auth-error"></p>

            <div class="table-wrap">
                <table class="result-table" aria-label="Meal logs">
                    <thead>
                        <tr>
                            <th>Food</th>
                            <th>Calories</th>
                            <th>Protein</th>
                            <th>Carbs</th>
                            <th>Fat</th>
                        </tr>
                    </thead>
                    <tbody id="mealLogBody">
                        <?php if (empty($recentMeals)): ?>
                            <tr>
                                <td colspan="5">No meals logged yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentMeals as $meal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)$meal['food_query'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$meal['calories'], ENT_QUOTES, 'UTF-8'); ?> kcal</td>
                                    <td><?php echo htmlspecialchars((string)$meal['protein_g'], ENT_QUOTES, 'UTF-8'); ?> g</td>
                                    <td><?php echo htmlspecialchars((string)$meal['carbs_g'], ENT_QUOTES, 'UTF-8'); ?> g</td>
                                    <td><?php echo htmlspecialchars((string)$meal['fat_g'], ENT_QUOTES, 'UTF-8'); ?> g</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>
