<main class="dashboard-shell">
    <aside class="dash-sidebar">
        <div class="dash-logo">FT</div>
        <ul class="dash-nav-list">
            <li><a href="index.php?route=dashboard">Home</a></li>
            <li><a href="index.php?route=dashboard">Meals</a></li>
            <li><a href="index.php?route=dashboard">Stats</a></li>
            <li class="active"><a href="index.php?route=profile">Profile</a></li>
        </ul>
    </aside>

    <section class="dash-content">
        <header class="dash-topbar">
            <div class="user-pill">
                <div class="avatar-circle"><?php echo strtoupper(substr((string)($currentUser['username'] ?? 'U'), 0, 1)); ?></div>
                <div>
                    <h2 id="profileName"><?php echo htmlspecialchars((string)($currentUser['username'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p id="profileEmail"><?php echo htmlspecialchars((string)($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="dash-top-actions">
                <a class="btn-ghost dashboard-exit" href="index.php?route=profile/settings">Settings</a>
                <a class="btn-ghost dashboard-exit" href="index.php?route=dashboard">Back to Dashboard</a>
                <button id="profileLogoutBtn" class="btn-ghost" type="button">Log Out</button>
            </div>
        </header>

        <section class="profile-panel">
            <h3>Body Data &amp; Target</h3>
            <p class="profile-sub">Keep your personal metrics updated for better tracking.</p>

            <div class="profile-bmi-row">
                <strong>BMI:</strong>
                <span id="profileBmiValue"><?php echo htmlspecialchars(isset($currentUser['bmi']) && $currentUser['bmi'] !== null ? (string)$currentUser['bmi'] : '-', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <form id="profileDataForm" class="profile-form" novalidate>
                <div class="field">
                    <label for="profileAge">Age</label>
                    <input
                        id="profileAge"
                        name="age"
                        type="number"
                        min="10"
                        max="100"
                        step="1"
                        value="<?php echo htmlspecialchars((string)($currentUser['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    />
                </div>

                <div class="field">
                    <label for="profileHeightCm">Height (cm)</label>
                    <input
                        id="profileHeightCm"
                        name="height_cm"
                        type="number"
                        min="100"
                        max="260"
                        step="0.1"
                        value="<?php echo htmlspecialchars((string)($currentUser['height_cm'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    />
                </div>

                <div class="field">
                    <label for="profileWeightKg">Weight (kg)</label>
                    <input
                        id="profileWeightKg"
                        name="weight_kg"
                        type="number"
                        min="20"
                        max="400"
                        step="0.1"
                        value="<?php echo htmlspecialchars((string)($currentUser['weight_kg'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    />
                </div>

                <div class="field full">
                    <label for="profileGoal">Target</label>
                    <select id="profileGoal" name="goal_preference" required>
                        <option value="">Select target</option>
                        <option value="deficit" <?php echo (($currentUser['goal_preference'] ?? '') === 'deficit') ? 'selected' : ''; ?>>Deficit</option>
                        <option value="gain" <?php echo (($currentUser['goal_preference'] ?? '') === 'gain') ? 'selected' : ''; ?>>Gain</option>
                    </select>
                </div>

                <div class="full profile-form-actions">
                    <button type="submit" class="btn-solid">Save Profile Data</button>
                </div>
            </form>

            <p id="profileDataMessage" class="profile-message" aria-live="polite"></p>
        </section>
    </section>
</main>
