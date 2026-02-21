<header class="navbar">
    <div class="nav-brand">
        <span class="brand-dot"></span>
        <h1>FitTrack Studio</h1>
    </div>
    <nav class="nav-actions">
        <button id="openLoginBtn" class="btn-ghost" type="button">Log In</button>
        <button id="openSignupBtn" class="btn-solid" type="button">Sign Up</button>
    </nav>
</header>

<main class="app">
    <h2>BMI + Calories Starter Calculator</h2>
    <p class="sub">Enter your details to calculate BMI and get starter daily calories for maintenance, fat loss (deficit), and weight gain.</p>

    <form id="fitnessForm" novalidate>
        <div class="grid">
            <div class="field">
                <label for="age">Age (years)</label>
                <input id="age" name="age" type="number" min="10" max="100" required />
            </div>

            <div class="field">
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            <div class="field">
                <label for="height">Height (cm)</label>
                <input id="height" name="height" type="number" min="100" max="250" step="0.1" required />
            </div>

            <div class="field">
                <label for="weight">Weight (kg)</label>
                <input id="weight" name="weight" type="number" min="20" max="300" step="0.1" required />
            </div>

            <div class="field full">
                <label for="activity">Activity Level</label>
                <select id="activity" name="activity" required>
                    <option value="">Select activity level</option>
                    <option value="1.2">Sedentary (little or no exercise)</option>
                    <option value="1.375">Lightly active (1-3 days/week)</option>
                    <option value="1.55">Moderately active (3-5 days/week)</option>
                    <option value="1.725">Very active (6-7 days/week)</option>
                    <option value="1.9">Extra active (hard training + physical job)</option>
                </select>
            </div>
        </div>

        <button class="calculate-btn" type="submit">Calculate</button>
        <p id="error" class="error"></p>
    </form>

    <section id="results" class="results" aria-live="polite">
        <div class="result-row">
            <h3>BMI Overview</h3>
            <div class="table-wrap">
                <table class="result-table" aria-label="BMI overview table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>BMI Score</td>
                            <td id="bmiValue">-</td>
                        </tr>
                        <tr>
                            <td>BMI Category</td>
                            <td id="bmiCategory">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="result-row">
            <h3>Daily Calories by Goal</h3>
            <div class="table-wrap">
                <table class="result-table" aria-label="Calories by goal">
                    <thead>
                        <tr>
                            <th>Goal Category</th>
                            <th>Target Calories</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Huge Deficit (aggressive fat loss)</td>
                            <td id="hugeDeficitText">-</td>
                        </tr>
                        <tr>
                            <td>Moderate Deficit (steady fat loss)</td>
                            <td id="moderateDeficitText">-</td>
                        </tr>
                        <tr>
                            <td>Mild Deficit (easy fat loss)</td>
                            <td id="mildDeficitText">-</td>
                        </tr>
                        <tr>
                            <td>Maintenance</td>
                            <td id="maintenanceText">-</td>
                        </tr>
                        <tr>
                            <td>Lean Gain (small surplus)</td>
                            <td id="leanGainText">-</td>
                        </tr>
                        <tr>
                            <td>Aggressive Gain (bigger surplus)</td>
                            <td id="aggressiveGainText">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="note">Starter recommendation: begin with small adjustments, track for 2-3 weeks, then update based on progress.</p>
            <button id="trackMealsBtn" class="btn-solid hidden" type="button">Track &amp; Log Meals</button>
            <p id="authStatus" class="auth-status hidden"></p>
        </div>
    </section>
</main>

<div id="modalBackdrop" class="modal-backdrop hidden"></div>

<section id="signupModal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="signupTitle">
    <div class="modal-card">
        <button class="modal-close" type="button" data-close="signupModal">&times;</button>
        <h3 id="signupTitle">Create your account</h3>
        <p class="modal-sub">Sign up to start tracking and logging your meals.</p>
        <form id="signupForm" class="auth-form" novalidate>
            <label for="signupUsername">Username</label>
            <input id="signupUsername" type="text" placeholder="Choose a username" required />

            <label for="signupEmail">Email</label>
            <input id="signupEmail" type="email" placeholder="name@email.com" required />

            <label for="signupPassword">Password</label>
            <input id="signupPassword" type="password" placeholder="At least 6 characters" minlength="6" required />

            <p id="signupError" class="auth-error"></p>
            <button class="btn-solid auth-submit" type="submit">Sign Up</button>
        </form>
        <p class="switch-auth">Already have an account? <button id="switchToLogin" class="link-btn" type="button">Log In</button></p>
    </div>
</section>

<section id="loginModal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <div class="modal-card">
        <button class="modal-close" type="button" data-close="loginModal">&times;</button>
        <h3 id="loginTitle">Welcome back</h3>
        <p class="modal-sub">Log in to continue tracking your meal and calorie goals.</p>
        <form id="loginForm" class="auth-form" novalidate>
            <label for="loginEmail">Email</label>
            <input id="loginEmail" type="email" placeholder="name@email.com" required />

            <label for="loginPassword">Password</label>
            <input id="loginPassword" type="password" placeholder="Your password" required />

            <p id="loginError" class="auth-error"></p>
            <button class="btn-solid auth-submit" type="submit">Log In</button>
        </form>
        <p class="switch-auth">New here? <button id="switchToSignup" class="link-btn" type="button">Create account</button></p>
    </div>
</section>
