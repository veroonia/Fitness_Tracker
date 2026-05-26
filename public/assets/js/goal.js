const goalForm = document.getElementById('goalForm');
const goalError = document.getElementById('goalError');
const FORM_STORAGE_KEY = 'fittrack-form-values';

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

goalForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    goalError.textContent = '';

    const selectedGoal = goalForm.querySelector('input[name="goal"]:checked');
    if (!selectedGoal) {
        goalError.textContent = 'Please choose your goal first.';
        return;
    }

    const body = new URLSearchParams({ goal: selectedGoal.value });
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
