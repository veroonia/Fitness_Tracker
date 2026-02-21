const goalForm = document.getElementById('goalForm');
const goalError = document.getElementById('goalError');

goalForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    goalError.textContent = '';

    const selectedGoal = goalForm.querySelector('input[name="goal"]:checked');
    if (!selectedGoal) {
        goalError.textContent = 'Please choose your goal first.';
        return;
    }

    const body = new URLSearchParams({ goal: selectedGoal.value });

    try {
        const response = await fetch('index.php?route=goals/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            goalError.textContent = data.message || 'Unable to save goal right now.';
            return;
        }

        window.location.href = data.redirectTo || 'index.php?route=dashboard';
    } catch (error) {
        goalError.textContent = 'Server error while saving your goal.';
    }
});
