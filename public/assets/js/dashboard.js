const foodLogForm = document.getElementById('foodLogForm');
const foodLogError = document.getElementById('foodLogError');
const mealLogBody = document.getElementById('mealLogBody');

const totalCaloriesValue = document.getElementById('totalCaloriesValue');
const totalProteinValue = document.getElementById('totalProteinValue');
const totalCarbsValue = document.getElementById('totalCarbsValue');
const totalFatValue = document.getElementById('totalFatValue');

const cardCaloriesValue = document.getElementById('cardCaloriesValue');
const cardProteinValue = document.getElementById('cardProteinValue');
const cardCarbsValue = document.getElementById('cardCarbsValue');
const cardFatValue = document.getElementById('cardFatValue');
const dashboardLogoutBtn = document.getElementById('dashboardLogoutBtn');
let dailyGoalCalories = Number(window.APP_CURRENT_USER?.daily_goal_calories ?? 0) || null;
const FITTRACK_FORM_STORAGE_KEY = 'fittrack-form-values';

function roundCalories(value) {
    return Math.round(value / 10) * 10;
}

function getStoredCalculatorValues() {
    const raw = localStorage.getItem(FITTRACK_FORM_STORAGE_KEY);
    if (!raw) {
        return null;
    }

    try {
        const values = JSON.parse(raw);
        if (!values || typeof values !== 'object') {
            return null;
        }

        const age = Number(values.age);
        const heightCm = Number(values.height);
        const weightKg = Number(values.weight);
        const sex = String(values.sex || '').trim();
        const activityFactor = Number(values.activity);

        if (!age || !heightCm || !weightKg || !sex || !activityFactor) {
            return null;
        }

        return {
            age,
            heightCm,
            weightKg,
            sex,
            activityFactor
        };
    } catch (error) {
        return null;
    }
}

function calculateMaintenanceCalories(profile) {
    if (profile.sex === 'male') {
        return roundCalories(((10 * profile.weightKg) + (6.25 * profile.heightCm) - (5 * profile.age) + 5) * profile.activityFactor);
    }

    if (profile.sex === 'female') {
        return roundCalories(((10 * profile.weightKg) + (6.25 * profile.heightCm) - (5 * profile.age) - 161) * profile.activityFactor);
    }

    const maleBmr = (10 * profile.weightKg) + (6.25 * profile.heightCm) - (5 * profile.age) + 5;
    const femaleBmr = (10 * profile.weightKg) + (6.25 * profile.heightCm) - (5 * profile.age) - 161;
    return roundCalories((((maleBmr + femaleBmr) / 2) * profile.activityFactor));
}

function calculateGoalCalories(profile, goal) {
    const maintenanceCalories = calculateMaintenanceCalories(profile);
    const caloriesPerKg = 7700;
    const goalOffsets = {
        maintain: 0,
        loss_mild: -(caloriesPerKg * 0.25 / 7),
        loss: -(caloriesPerKg * 0.5 / 7),
        loss_extreme: -(caloriesPerKg * 1 / 7),
        gain_mild: caloriesPerKg * 0.25 / 7,
        gain: caloriesPerKg * 0.5 / 7,
        gain_fast: caloriesPerKg * 1 / 7,
        deficit: -(caloriesPerKg * 0.5 / 7)
    };

    return roundCalories(maintenanceCalories + (goalOffsets[goal] ?? 0));
}

const storedCalculatorValues = getStoredCalculatorValues();
if (storedCalculatorValues && window.APP_CURRENT_USER?.goal_preference) {
    dailyGoalCalories = calculateGoalCalories(storedCalculatorValues, window.APP_CURRENT_USER.goal_preference);
}

function formatNumber(value) {
    const number = Number(value) || 0;
    return Number.isInteger(number) ? String(number) : number.toFixed(1);
}

function formatCalories(calories) {
    const todayCalories = formatNumber(calories);

    if (dailyGoalCalories) {
        return `${todayCalories} / ${dailyGoalCalories} kcal`;
    }

    return `${todayCalories} kcal`;
}

function renderTotals(totals) {
    totalCaloriesValue.textContent = formatCalories(totals.calories);
    if (totalProteinValue) {
        totalProteinValue.textContent = `${formatNumber(totals.protein_g)}g`;
    }
    if (totalCarbsValue) {
        totalCarbsValue.textContent = `${formatNumber(totals.carbs_g)}g`;
    }
    if (totalFatValue) {
        totalFatValue.textContent = `${formatNumber(totals.fat_g)}g`;
    }

    cardCaloriesValue.textContent = formatCalories(totals.calories);
    cardProteinValue.textContent = `${formatNumber(totals.protein_g)} g`;
    cardCarbsValue.textContent = `${formatNumber(totals.carbs_g)} g`;
    cardFatValue.textContent = `${formatNumber(totals.fat_g)} g`;
}

function prependMeal(entry) {
    const emptyRow = mealLogBody.querySelector('td[colspan="6"]');
    if (emptyRow) {
        mealLogBody.innerHTML = '';
    }

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${entry.food_query}</td>
        <td>${entry.calories} kcal</td>
        <td>${entry.protein_g} g</td>
        <td>${entry.carbs_g} g</td>
        <td>${entry.fat_g} g</td>
        <td><button class="delete-btn btn-ghost" data-id="${entry.id}" type="button">Delete</button></td>
    `;
    if (entry.id) {
        row.setAttribute('data-id', entry.id);
    }

    mealLogBody.prepend(row);
}

foodLogForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    foodLogError.textContent = '';

    const foodTextInput = document.getElementById('foodText');
    const foodText = foodTextInput.value.trim();

    if (!foodText) {
        foodLogError.textContent = 'Please describe a meal first.';
        return;
    }

    const body = new URLSearchParams({ food_text: foodText });

    try {
        const response = await fetch('index.php?route=dashboard/log-food', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            foodLogError.textContent = data.message || 'Unable to analyze this food.';
            return;
        }

        prependMeal(data.entry);
        renderTotals(data.totals);
        foodLogForm.reset();
    } catch (error) {
        foodLogError.textContent = 'Server error while logging food.';
    }
});

// Delegate delete button clicks
mealLogBody.addEventListener('click', async function (event) {
    const btn = event.target.closest ? event.target.closest('.delete-btn') : null;
    if (!btn) return;

    const id = btn.getAttribute('data-id');
    if (!id) return;

    if (!confirm('Delete this meal entry?')) return;

    try {
        const body = new URLSearchParams({ id });
        const resp = await fetch('index.php?route=dashboard/delete-food', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });

        const text = await resp.text();
        let data = null;
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            console.error('Delete response parse error:', parseErr, 'raw:', text);
            alert('Server returned unexpected response: ' + (text || resp.statusText));
            return;
        }

        if (!resp.ok || !data.success) {
            alert(data.message || 'Unable to delete meal.');
            return;
        }

        // Remove the row from DOM
        const row = btn.closest('tr');
        if (row) row.remove();

        // If no rows left, show the empty state
        if (!mealLogBody.querySelector('tr')) {
            mealLogBody.innerHTML = '<tr><td colspan="6">No meals logged yet.</td></tr>';
        }

        // Update totals
        if (data.totals) {
            renderTotals(data.totals);
        }
    } catch (err) {
        console.error('Delete request failed:', err);
        //alert('Server error while deleting meal. It may have succeeded—check the list or refresh to confirm.');
    }
});

dashboardLogoutBtn.addEventListener('click', async function () {
    try {
        await fetch('index.php?route=auth/logout', {
            method: 'POST'
        });
    } catch (error) {
    }

    window.location.href = 'index.php?route=home';
});
