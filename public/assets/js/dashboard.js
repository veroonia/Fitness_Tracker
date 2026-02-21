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

function renderTotals(totals) {
    totalCaloriesValue.textContent = totals.calories;
    totalProteinValue.textContent = `${totals.protein_g}g`;
    totalCarbsValue.textContent = `${totals.carbs_g}g`;
    totalFatValue.textContent = `${totals.fat_g}g`;

    cardCaloriesValue.textContent = `${totals.calories} kcal`;
    cardProteinValue.textContent = `${totals.protein_g} g`;
    cardCarbsValue.textContent = `${totals.carbs_g} g`;
    cardFatValue.textContent = `${totals.fat_g} g`;
}

function prependMeal(entry) {
    const emptyRow = mealLogBody.querySelector('td[colspan="5"]');
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
    `;

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

dashboardLogoutBtn.addEventListener('click', async function () {
    try {
        await fetch('index.php?route=auth/logout', {
            method: 'POST'
        });
    } catch (error) {
    }

    window.location.href = 'index.php?route=home';
});
