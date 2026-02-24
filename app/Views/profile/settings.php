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
                    <h2 id="settingsName"><?php echo htmlspecialchars((string)($currentUser['username'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p id="settingsEmail"><?php echo htmlspecialchars((string)($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="dash-top-actions">
                <a class="btn-ghost dashboard-exit" href="index.php?route=profile">Back to Profile</a>
                <button id="settingsLogoutBtn" class="btn-ghost" type="button">Log Out</button>
            </div>
        </header>

        <section class="settings-grid">
            <article class="settings-card">
                <h3>Add Account</h3>
                <p>Create another user account.</p>
                <form id="addAccountForm" class="profile-form" novalidate>
                    <div class="field">
                        <label for="addUsername">Username</label>
                        <input id="addUsername" name="username" type="text" required />
                    </div>
                    <div class="field">
                        <label for="addEmail">Email</label>
                        <input id="addEmail" name="email" type="email" required />
                    </div>
                    <div class="field full">
                        <label for="addPassword">Password</label>
                        <input id="addPassword" name="password" type="password" minlength="6" required />
                    </div>
                    <div class="full profile-form-actions">
                        <button type="submit" class="btn-solid">Add Account</button>
                    </div>
                </form>
                <p id="addAccountMessage" class="profile-message" aria-live="polite"></p>
            </article>

            <article class="settings-card">
                <h3>Edit Account</h3>
                <p>Update your username, email, or password.</p>
                <form id="editAccountForm" class="profile-form" novalidate>
                    <div class="field">
                        <label for="editUsername">Username</label>
                        <input id="editUsername" name="username" type="text" value="<?php echo htmlspecialchars((string)($currentUser['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>
                    <div class="field">
                        <label for="editEmail">Email</label>
                        <input id="editEmail" name="email" type="email" value="<?php echo htmlspecialchars((string)($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>
                    <div class="field full">
                        <label for="editPassword">New Password (optional)</label>
                        <input id="editPassword" name="password" type="password" minlength="6" placeholder="Leave blank to keep current password" />
                    </div>
                    <div class="full profile-form-actions">
                        <button type="submit" class="btn-solid">Save Account Changes</button>
                    </div>
                </form>
                <p id="editAccountMessage" class="profile-message" aria-live="polite"></p>
            </article>

            <article class="settings-card settings-danger">
                <h3>Delete Account</h3>
                <p>Type DELETE to permanently remove your account and meal logs.</p>
                <form id="deleteAccountForm" class="profile-form" novalidate>
                    <div class="field full">
                        <label for="confirmDelete">Confirm</label>
                        <input id="confirmDelete" name="confirm_delete" type="text" placeholder="Type DELETE" required />
                    </div>
                    <div class="full profile-form-actions">
                        <button type="submit" class="btn-ghost">Delete Account</button>
                    </div>
                </form>
                <p id="deleteAccountMessage" class="profile-message" aria-live="polite"></p>
            </article>
        </section>
    </section>
</main>
