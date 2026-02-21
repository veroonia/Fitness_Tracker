const form = document.getElementById('fitnessForm');
const results = document.getElementById('results');
const errorText = document.getElementById('error');

const bmiValue = document.getElementById('bmiValue');
const bmiCategoryCell = document.getElementById('bmiCategory');
const hugeDeficitText = document.getElementById('hugeDeficitText');
const moderateDeficitText = document.getElementById('moderateDeficitText');
const mildDeficitText = document.getElementById('mildDeficitText');
const maintenanceText = document.getElementById('maintenanceText');
const leanGainText = document.getElementById('leanGainText');
const aggressiveGainText = document.getElementById('aggressiveGainText');

const trackMealsBtn = document.getElementById('trackMealsBtn');
const authStatus = document.getElementById('authStatus');

const modalBackdrop = document.getElementById('modalBackdrop');
const signupModal = document.getElementById('signupModal');
const loginModal = document.getElementById('loginModal');

const openSignupBtn = document.getElementById('openSignupBtn');
const openLoginBtn = document.getElementById('openLoginBtn');
const logoutBtn = document.getElementById('logoutBtn');
const switchToLogin = document.getElementById('switchToLogin');
const switchToSignup = document.getElementById('switchToSignup');

const signupForm = document.getElementById('signupForm');
const loginForm = document.getElementById('loginForm');
const signupError = document.getElementById('signupError');
const loginError = document.getElementById('loginError');

let currentUser = window.APP_CURRENT_USER || null;
const FORM_STORAGE_KEY = 'fittrack-form-values';

const persistedFields = {
    age: document.getElementById('age'),
    sex: document.getElementById('sex'),
    height: document.getElementById('height'),
    weight: document.getElementById('weight'),
    activity: document.getElementById('activity')
};

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
        logoutBtn.classList.remove('hidden');
        if (currentUser.goal_preference) {
            trackMealsBtn.textContent = 'Open Dashboard';
        }
        return;
    }

    openSignupBtn.textContent = 'Sign Up';
    openSignupBtn.disabled = false;
    openLoginBtn.textContent = 'Log In';
    openLoginBtn.disabled = false;
    logoutBtn.classList.add('hidden');
}

function showStatus(message) {
    authStatus.textContent = message;
    authStatus.classList.remove('hidden');
}

function saveFormValues() {
    const formValues = {
        age: persistedFields.age.value,
        sex: persistedFields.sex.value,
        height: persistedFields.height.value,
        weight: persistedFields.weight.value,
        activity: persistedFields.activity.value
    };

    localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(formValues));
}

function restoreFormValues() {
    const raw = localStorage.getItem(FORM_STORAGE_KEY);
    if (!raw) {
        return;
    }

    try {
        const formValues = JSON.parse(raw);
        if (!formValues || typeof formValues !== 'object') {
            return;
        }

        Object.keys(persistedFields).forEach(function (fieldName) {
            if (typeof formValues[fieldName] === 'string') {
                persistedFields[fieldName].value = formValues[fieldName];
            }
        });
    } catch (error) {
        localStorage.removeItem(FORM_STORAGE_KEY);
    }
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
    const hugeDeficitCalories = roundCalories(maintenanceCalories * 0.70);
    const moderateDeficitCalories = roundCalories(maintenanceCalories * 0.80);
    const mildDeficitCalories = roundCalories(maintenanceCalories * 0.90);
    const leanGainCalories = roundCalories(maintenanceCalories * 1.10);
    const aggressiveGainCalories = roundCalories(maintenanceCalories * 1.15);

    bmiValue.textContent = bmi.toFixed(1);
    bmiValue.className = 'result-emphasis';
    bmiCategoryCell.innerHTML = `<span class="tag ${bmiInfo.className}">${bmiInfo.label}</span>`;

    hugeDeficitText.innerHTML = `<span class="result-emphasis">${hugeDeficitCalories} kcal/day</span>`;
    moderateDeficitText.innerHTML = `<span class="result-emphasis">${moderateDeficitCalories} kcal/day</span>`;
    mildDeficitText.innerHTML = `<span class="result-emphasis">${mildDeficitCalories} kcal/day</span>`;
    maintenanceText.innerHTML = `<span class="result-emphasis">${maintenanceCalories} kcal/day</span>`;
    leanGainText.innerHTML = `<span class="result-emphasis">${leanGainCalories} kcal/day</span>`;
    aggressiveGainText.innerHTML = `<span class="result-emphasis">${aggressiveGainCalories} kcal/day</span>`;

    results.style.display = 'block';
    trackMealsBtn.classList.remove('hidden');
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    saveFormValues();
});

Object.values(persistedFields).forEach(function (field) {
    field.addEventListener('input', saveFormValues);
    field.addEventListener('change', saveFormValues);
});

trackMealsBtn.addEventListener('click', function () {
    if (currentUser && currentUser.username) {
        if (currentUser.goal_preference) {
            window.location.href = 'index.php?route=dashboard';
            return;
        }
        window.location.href = 'index.php?route=goals';
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

logoutBtn.addEventListener('click', async function () {
    try {
        await fetch('index.php?route=auth/logout', {
            method: 'POST'
        });
    } catch (error) {
    }

    currentUser = null;
    updateAuthUI();
    window.location.href = 'index.php?route=home';
});

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
        showStatus('Account created. Redirecting to goal setup...');
        signupForm.reset();
        closeAllModals();
        window.location.href = data.redirectTo || 'index.php?route=goals';
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
        showStatus(`Welcome back ${currentUser.username}. Redirecting...`);
        loginForm.reset();
        closeAllModals();
        window.location.href = data.redirectTo || 'index.php?route=dashboard';
    } catch (error) {
        loginError.textContent = 'Server connection failed. Check Apache/MySQL.';
    }
});

updateAuthUI();
restoreFormValues();
