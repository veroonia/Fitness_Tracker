const form = document.getElementById('fitnessForm');
const results = document.getElementById('results');
const errorText = document.getElementById('error');

const bmiValue = document.getElementById('bmiValue');
const bmiCategoryCell = document.getElementById('bmiCategory');
const maintenanceText = document.getElementById('maintenanceText');
const deficitText = document.getElementById('deficitText');
const gainText = document.getElementById('gainText');

const categoryRows = {
    Underweight: document.getElementById('cat-underweight'),
    'Normal weight': document.getElementById('cat-normal'),
    Overweight: document.getElementById('cat-overweight'),
    Obesity: document.getElementById('cat-obesity')
};

const trackMealsBtn = document.getElementById('trackMealsBtn');
const authStatus = document.getElementById('authStatus');

const modalBackdrop = document.getElementById('modalBackdrop');
const signupModal = document.getElementById('signupModal');
const loginModal = document.getElementById('loginModal');

const openSignupBtn = document.getElementById('openSignupBtn');
const openLoginBtn = document.getElementById('openLoginBtn');
const switchToLogin = document.getElementById('switchToLogin');
const switchToSignup = document.getElementById('switchToSignup');

const signupForm = document.getElementById('signupForm');
const loginForm = document.getElementById('loginForm');
const signupError = document.getElementById('signupError');
const loginError = document.getElementById('loginError');

let currentUser = window.APP_CURRENT_USER || null;

function getBmiCategory(bmi) {
    if (bmi < 18.5) {
        return { label: 'Underweight', className: 'warn' };
    }
    if (bmi < 25) {
        return { label: 'Normal weight', className: 'ok' };
    }
    if (bmi < 30) {
        return { label: 'Overweight', className: 'warn' };
    }
    return { label: 'Obesity', className: 'danger' };
}

function roundCalories(value) {
    return Math.round(value / 10) * 10;
}

function clearCategoryHighlights() {
    Object.values(categoryRows).forEach(function (row) {
        row.classList.remove('category-active');
    });
}

function closeAllModals() {
    signupModal.classList.add('hidden');
    loginModal.classList.add('hidden');
    modalBackdrop.classList.add('hidden');
}

function openModal(type) {
    closeAllModals();
    if (type === 'signup') {
        signupModal.classList.remove('hidden');
    }
    if (type === 'login') {
        loginModal.classList.remove('hidden');
    }
    modalBackdrop.classList.remove('hidden');
}

function updateAuthUI() {
    if (currentUser && currentUser.username) {
        openSignupBtn.textContent = currentUser.username;
        openSignupBtn.disabled = true;
        openLoginBtn.textContent = 'Logged In';
        openLoginBtn.disabled = true;
    }
}

function showStatus(message) {
    authStatus.textContent = message;
    authStatus.classList.remove('hidden');
}

form.addEventListener('submit', function (event) {
    event.preventDefault();
    errorText.textContent = '';

    const age = Number(document.getElementById('age').value);
    const sex = document.getElementById('sex').value;
    const heightCm = Number(document.getElementById('height').value);
    const weightKg = Number(document.getElementById('weight').value);
    const activityFactor = Number(document.getElementById('activity').value);

    if (!age || !sex || !heightCm || !weightKg || !activityFactor) {
        results.style.display = 'none';
        errorText.textContent = 'Please fill in all fields with valid values.';
        return;
    }

    const heightM = heightCm / 100;
    const bmi = weightKg / (heightM * heightM);
    const bmiInfo = getBmiCategory(bmi);

    let bmr;
    if (sex === 'male') {
        bmr = (10 * weightKg) + (6.25 * heightCm) - (5 * age) + 5;
    } else {
        bmr = (10 * weightKg) + (6.25 * heightCm) - (5 * age) - 161;
    }

    const maintenanceCalories = roundCalories(bmr * activityFactor);
    const deficitCalories = roundCalories(maintenanceCalories * 0.85);
    const gainCalories = roundCalories(maintenanceCalories * 1.10);

    bmiValue.textContent = bmi.toFixed(1);
    bmiValue.className = 'result-emphasis';
    bmiCategoryCell.innerHTML = `<span class="tag ${bmiInfo.className}">${bmiInfo.label}</span>`;

    maintenanceText.innerHTML = `<span class="result-emphasis">${maintenanceCalories} kcal/day</span>`;
    deficitText.innerHTML = `<span class="result-emphasis">${deficitCalories} kcal/day</span>`;
    gainText.innerHTML = `<span class="result-emphasis">${gainCalories} kcal/day</span>`;

    clearCategoryHighlights();
    const currentCategoryRow = categoryRows[bmiInfo.label];
    if (currentCategoryRow) {
        currentCategoryRow.classList.add('category-active');
    }

    results.style.display = 'block';
    trackMealsBtn.classList.remove('hidden');
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

trackMealsBtn.addEventListener('click', function () {
    if (currentUser && currentUser.username) {
        showStatus(`Welcome ${currentUser.username}. Meal tracking screen is the next module.`);
        return;
    }
    openModal('signup');
});

openSignupBtn.addEventListener('click', function () {
    if (!currentUser) {
        openModal('signup');
    }
});

openLoginBtn.addEventListener('click', function () {
    if (!currentUser) {
        openModal('login');
    }
});

switchToLogin.addEventListener('click', function () {
    signupError.textContent = '';
    openModal('login');
});

switchToSignup.addEventListener('click', function () {
    loginError.textContent = '';
    openModal('signup');
});

document.querySelectorAll('.modal-close').forEach(function (button) {
    button.addEventListener('click', closeAllModals);
});

modalBackdrop.addEventListener('click', closeAllModals);

signupForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    signupError.textContent = '';

    const username = document.getElementById('signupUsername').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const password = document.getElementById('signupPassword').value;

    const body = new URLSearchParams({ username, email, password });

    try {
        const response = await fetch('index.php?route=auth/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            signupError.textContent = data.message || 'Sign up failed.';
            return;
        }

        currentUser = data.user;
        updateAuthUI();
        showStatus('Account created. You can now track and log meals.');
        signupForm.reset();
        closeAllModals();
    } catch (error) {
        signupError.textContent = 'Server connection failed. Check Apache/MySQL.';
    }
});

loginForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    loginError.textContent = '';

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    const body = new URLSearchParams({ email, password });

    try {
        const response = await fetch('index.php?route=auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            loginError.textContent = data.message || 'Login failed.';
            return;
        }

        currentUser = data.user;
        updateAuthUI();
        showStatus(`Welcome back ${currentUser.username}.`);
        loginForm.reset();
        closeAllModals();
    } catch (error) {
        loginError.textContent = 'Server connection failed. Check Apache/MySQL.';
    }
});

updateAuthUI();
