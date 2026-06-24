const goalForm = document.getElementById('goalForm');
const goalError = document.getElementById('goalError');
const goalDetailSection = document.getElementById('goalDetailSection');
const primaryGoalInputs = goalForm.querySelectorAll('input[name="primary_goal"]');
const detailOptions = goalForm.querySelectorAll('.detail-option');
const detailGoalInputs = goalForm.querySelectorAll('input[name="goal"]');
const FORM_STORAGE_KEY = 'fittrack-form-values';

function updateDetailOptions(primaryGoal) {
    goalError.textContent = '';

    detailOptions.forEach(function (option) {
        const group = option.getAttribute('data-group');
        const input = option.querySelector('input[name="goal"]');
        const shouldShow = group === primaryGoal;

        option.classList.toggle('hidden', !shouldShow);
        input.disabled = !shouldShow;

        if (!shouldShow) {
            input.checked = false;
        }
    });

    goalDetailSection.classList.remove('hidden');
}

function getSavedProfileValues() {
    const raw = localStorage.getItem(FORM_STORAGE_KEY);
    if (!raw) {
        return null;
    }

    try {
        const savedValues = JSON.parse(raw);
        if (!savedValues || typeof savedValues !== 'object') {
            return null;
        }

        const profileValues = {
            age: String(savedValues.age || '').trim(),
            height_cm: String(savedValues.height || '').trim(),
            weight_kg: String(savedValues.weight || '').trim()
        };

        if (!profileValues.age || !profileValues.height_cm || !profileValues.weight_kg) {
            return null;
        }

        return profileValues;
    } catch (error) {
        return null;
    }
}

function appendSavedProfileValues(body) {
    const profileValues = getSavedProfileValues();
    if (!profileValues) {
        return;
    }

    Object.entries(profileValues).forEach(function ([key, value]) {
        body.append(key, value);
    });
}

primaryGoalInputs.forEach(function (input) {
    input.addEventListener('change', function () {
        updateDetailOptions(input.value);
    });
});

goalForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    goalError.textContent = '';

    const selectedPrimaryGoal = goalForm.querySelector('input[name="primary_goal"]:checked');
    if (!selectedPrimaryGoal) {
        goalError.textContent = 'Please choose maintenance, loss, or gain first.';
        return;
    }

    const selectedGoal = goalForm.querySelector('input[name="goal"]:checked');
    if (!selectedGoal) {
        goalError.textContent = 'Please choose a pace for your selected goal.';
        return;
    }

    const body = new URLSearchParams({
        primary_goal: selectedPrimaryGoal.value,
        goal: selectedGoal.value
    });
    appendSavedProfileValues(body);

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

// Handle browser back-cache restore where input might already be selected.
const preselectedPrimaryGoal = goalForm.querySelector('input[name="primary_goal"]:checked');
if (preselectedPrimaryGoal) {
    updateDetailOptions(preselectedPrimaryGoal.value);
} else {
    detailGoalInputs.forEach(function (input) {
        input.disabled = true;
    });
}
