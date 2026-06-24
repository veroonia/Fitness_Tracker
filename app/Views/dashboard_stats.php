<main class="dashboard-shell">
    <aside class="dash-sidebar">
        <div class="dash-logo">FT</div>
        <ul class="dash-nav-list">
            <li><a href="index.php?route=dashboard">Home</a></li>
            <li><a href="index.php?route=meals">Meals</a></li>
            <li class="active">Stats</li>
            <li><a href="index.php?route=profile">Profile</a></li>
        </ul>
    </aside>

    <section class="dash-content">
        <header class="dash-topbar">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr((string)($currentUser['username'] ?? 'U'), 0, 1)); ?></div>
                <div>
                    <h2><?php echo htmlspecialchars((string)($currentUser['username'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p>Daily calorie totals by log date</p>
                </div>
            </div>
            <div class="dash-top-actions">
                <a class="btn-ghost dashboard-exit" href="index.php?route=dashboard">Back to Dashboard</a>
                <button id="statsLogoutBtn" class="btn-ghost" type="button">Log Out</button>
            </div>
        </header>

        <section class="stats-panel">
            <div class="stats-header">
                <a class="btn-ghost" href="index.php?route=dashboard/stats&amp;month=<?php echo htmlspecialchars($previousMonth, ENT_QUOTES, 'UTF-8'); ?>">Previous</a>
                <h3><?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></h3>
                <a class="btn-ghost" href="index.php?route=dashboard/stats&amp;month=<?php echo htmlspecialchars($nextMonth, ENT_QUOTES, 'UTF-8'); ?>">Next</a>
            </div>

            <div class="calendar-grid calendar-weekdays" aria-hidden="true">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>

            <div class="calendar-grid calendar-days">
                <?php foreach ($calendarWeeks as $week): ?>
                    <?php foreach ($week as $day): ?>
                        <?php
                        $dayClass = $day['is_current_month'] ? 'calendar-day' : 'calendar-day muted';
                        $hasCalories = $day['calories'] !== null;
                        ?>
                        <article class="<?php echo $dayClass; ?>">
                            <span class="calendar-date"><?php echo htmlspecialchars((string)$day['day'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($hasCalories): ?>
                                <strong><?php echo htmlspecialchars(number_format((float)$day['calories'], 0), ENT_QUOTES, 'UTF-8'); ?> kcal</strong>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </section>
</main>
