<main class="goal-page">
    <section class="goal-card">
        <h1>Choose Your Primary Goal</h1>
        <p class="goal-sub">Pick your target once. You can change it later from settings.</p>

        <form id="goalForm" class="goal-form" novalidate>
            <label class="goal-option">
                <input type="radio" name="goal" value="deficit" required />
                <span>
                    <strong>Deficit</strong>
                    <small>Focus on fat loss with calorie deficit targets.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="goal" value="gain" required />
                <span>
                    <strong>Gain</strong>
                    <small>Focus on muscle and weight gain with surplus targets.</small>
                </span>
            </label>

            <p id="goalError" class="auth-error"></p>
            <button type="submit" class="btn-solid goal-submit">Save Goal &amp; Continue</button>
        </form>
    </section>
</main>
