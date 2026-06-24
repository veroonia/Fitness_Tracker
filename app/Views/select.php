<main class="goal-page">
    <section class="goal-card">
        <h1>Choose Your Primary Goal</h1>
        <p class="goal-sub">Pick your target once. You can change it later from settings.</p>

        <form id="goalForm" class="goal-form" novalidate>
            <label class="goal-option">
                <input type="radio" name="goal" value="loss_extreme" required />
                <span>
                    <strong>Huge Deficit</strong>
                    <small>Aggressive fat-loss target around 70% of maintenance calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="loss" required />
                <span>
                    <strong>Moderate Deficit</strong>
                    <small>Steady fat-loss target around 80% of maintenance calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="loss_mild" required />
                <span>
                    <strong>Mild Deficit</strong>
                    <small>Easy fat-loss target around 90% of maintenance calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="maintain" required />
                <span>
                    <strong>Maintenance</strong>
                    <small>Keep your current body weight with balanced daily calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="gain_mild" required />
                <span>
                    <strong>Lean Gain</strong>
                    <small>Small surplus around 110% of maintenance calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="gain" required />
                <span>
                    <strong>Aggressive Gain</strong>
                    <small>Bigger surplus around 115% of maintenance calories.</small>
                </span>
            </label>

            <p id="goalError" class="auth-error"></p>
            <button type="submit" class="btn-solid goal-submit">Save Goal &amp; Continue</button>
        </form>
    </section>
</main>
