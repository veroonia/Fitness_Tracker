<main class="goal-page">
    <section class="goal-card">
        <h1>Choose Your Primary Goal</h1>
        <p class="goal-sub">First choose maintenance, loss, or gain. Then choose a matching pace.</p>

        <form id="goalForm" class="goal-form" novalidate>
            <label class="goal-option">
                <input type="radio" name="primary_goal" value="maintain" required />
                <span>
                    <strong>Maintain Weight</strong>
                    <small>Keep your current body weight with maintenance calories.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="primary_goal" value="loss" required />
                <span>
                    <strong>Lose Weight</strong>
                    <small>Use a calorie deficit to reduce body weight over time.</small>
                </span>
            </label>

            <label class="goal-option">
                <input type="radio" name="primary_goal" value="gain" required />
                <span>
                    <strong>Gain Weight</strong>
                    <small>Use a calorie surplus for weight and muscle gain.</small>
                </span>
            </label>

            <fieldset id="goalDetailSection" class="goal-detail hidden" aria-live="polite">
                <legend>Choose your pace</legend>

                <label class="goal-option detail-option" data-group="maintain">
                    <input type="radio" name="goal" value="maintain" />
                    <span>
                        <strong>Maintain Weight</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="loss">
                    <input type="radio" name="goal" value="loss_mild" />
                    <span>
                        <strong>Mild Weight Loss (0.25 kg/week)</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="loss">
                    <input type="radio" name="goal" value="loss" />
                    <span>
                        <strong>Weight Loss (0.5 kg/week)</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="loss">
                    <input type="radio" name="goal" value="loss_extreme" />
                    <span>
                        <strong>Extreme Weight Loss (1 kg/week)</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="gain">
                    <input type="radio" name="goal" value="gain_mild" />
                    <span>
                        <strong>Mild Weight Gain (0.25 kg/week)</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="gain">
                    <input type="radio" name="goal" value="gain" />
                    <span>
                        <strong>Weight Gain (0.5 kg/week)</strong>
                    </span>
                </label>

                <label class="goal-option detail-option" data-group="gain">
                    <input type="radio" name="goal" value="gain_fast" />
                    <span>
                        <strong>Fast Weight Gain (1 kg/week)</strong>
                    </span>
                </label>
            </fieldset>

            <p id="goalError" class="auth-error"></p>
            <button type="submit" class="btn-solid goal-submit">Save Goal &amp; Continue</button>
        </form>
    </section>
</main>
