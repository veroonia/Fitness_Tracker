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

const modalBackdrop = document.getElementById('modalBackdrop');
const onboardingModal = document.getElementById('onboardingModal');
const signupModal = document.getElementById('signupModal');
const loginModal = document.getElementById('loginModal');

const openSignupBtn = document.getElementById('openSignupBtn');
const openLoginBtn = document.getElementById('openLoginBtn');
const trackYesBtn = document.getElementById('trackYesBtn');
const trackNoBtn = document.getElementById('trackNoBtn');

const signupForm = document.getElementById('signupForm');
const loginForm = document.getElementById('loginForm');
const signupError = document.getElementById('signupError');
const loginError = document.getElementById('loginError');
const switchToLogin = document.getElementById('switchToLogin');
const switchToSignup = document.getElementById('switchToSignup');

const MODAL_IDS = ['onboardingModal', 'signupModal', 'loginModal'];
const STORAGE_USERS_KEY = 'fittrack-users';
const STORAGE_SESSION_KEY = 'fittrack-current-user';
const STORAGE_ONBOARDING_KEY = 'fittrack-onboarding-seen';

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

function getStoredUsers() {
	const raw = localStorage.getItem(STORAGE_USERS_KEY);
	if (!raw) {
		return [];
	}

	try {
		const parsed = JSON.parse(raw);
		return Array.isArray(parsed) ? parsed : [];
	} catch (error) {
		return [];
	}
}

function saveUsers(users) {
	localStorage.setItem(STORAGE_USERS_KEY, JSON.stringify(users));
}

function setCurrentUser(user) {
	localStorage.setItem(STORAGE_SESSION_KEY, JSON.stringify(user));
	openSignupBtn.textContent = user.name;
	openSignupBtn.disabled = true;
	openLoginBtn.textContent = 'Logged In';
	openLoginBtn.disabled = true;
}

function syncSessionFromStorage() {
	const raw = localStorage.getItem(STORAGE_SESSION_KEY);
	if (!raw) {
		return;
	}

	try {
		const user = JSON.parse(raw);
		if (user && user.name) {
			setCurrentUser(user);
		}
	} catch (error) {
		localStorage.removeItem(STORAGE_SESSION_KEY);
	}
}

function closeAllModals() {
	MODAL_IDS.forEach(function (modalId) {
		document.getElementById(modalId).classList.add('hidden');
	});
	modalBackdrop.classList.add('hidden');
}

function openModal(modalId) {
	closeAllModals();
	document.getElementById(modalId).classList.remove('hidden');
	modalBackdrop.classList.remove('hidden');
}

function showOnboardingIfNeeded() {
	const wasSeen = localStorage.getItem(STORAGE_ONBOARDING_KEY);
	const hasSession = localStorage.getItem(STORAGE_SESSION_KEY);
	if (!wasSeen && !hasSession) {
		openModal('onboardingModal');
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
});

openSignupBtn.addEventListener('click', function () {
	if (!openSignupBtn.disabled) {
		openModal('signupModal');
	}
});

openLoginBtn.addEventListener('click', function () {
	if (!openLoginBtn.disabled) {
		openModal('loginModal');
	}
});

trackYesBtn.addEventListener('click', function () {
	localStorage.setItem(STORAGE_ONBOARDING_KEY, '1');
	openModal('signupModal');
});

trackNoBtn.addEventListener('click', function () {
	localStorage.setItem(STORAGE_ONBOARDING_KEY, '1');
	closeAllModals();
});

document.querySelectorAll('.modal-close').forEach(function (button) {
	button.addEventListener('click', function () {
		closeAllModals();
	});
});

modalBackdrop.addEventListener('click', function () {
	closeAllModals();
});

switchToLogin.addEventListener('click', function () {
	signupError.textContent = '';
	openModal('loginModal');
});

switchToSignup.addEventListener('click', function () {
	loginError.textContent = '';
	openModal('signupModal');
});

signupForm.addEventListener('submit', function (event) {
	event.preventDefault();
	signupError.textContent = '';

	const name = document.getElementById('signupName').value.trim();
	const email = document.getElementById('signupEmail').value.trim().toLowerCase();
	const password = document.getElementById('signupPassword').value;

	if (!name || !email || password.length < 6) {
		signupError.textContent = 'Please enter valid name, email, and password (min 6 characters).';
		return;
	}

	const users = getStoredUsers();
	const existingUser = users.find(function (user) {
		return user.email === email;
	});

	if (existingUser) {
		signupError.textContent = 'This email is already registered. Please log in.';
		return;
	}

	const newUser = { name, email, password };
	users.push(newUser);
	saveUsers(users);
	setCurrentUser({ name, email });
	localStorage.setItem(STORAGE_ONBOARDING_KEY, '1');
	signupForm.reset();
	closeAllModals();
});

loginForm.addEventListener('submit', function (event) {
	event.preventDefault();
	loginError.textContent = '';

	const email = document.getElementById('loginEmail').value.trim().toLowerCase();
	const password = document.getElementById('loginPassword').value;

	if (!email || !password) {
		loginError.textContent = 'Please enter your email and password.';
		return;
	}

	const users = getStoredUsers();
	const matchedUser = users.find(function (user) {
		return user.email === email && user.password === password;
	});

	if (!matchedUser) {
		loginError.textContent = 'Incorrect email or password. Try again.';
		return;
	}

	setCurrentUser({ name: matchedUser.name, email: matchedUser.email });
	loginForm.reset();
	closeAllModals();
});

syncSessionFromStorage();
showOnboardingIfNeeded();
