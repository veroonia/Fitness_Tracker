const profileDataForm = document.getElementById('profileDataForm');
const profileDataMessage = document.getElementById('profileDataMessage');
const profileLogoutBtn = document.getElementById('profileLogoutBtn');
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

function updateBmiPreview(heightCm, weightKg) {
    const bmiElement = document.getElementById('profileBmiValue');
    const height = Number(heightCm);
    const weight = Number(weightKg);

    if (!bmiElement || !height || !weight) {
        return;
    }

    const heightM = height / 100;
    bmiElement.textContent = (weight / (heightM * heightM)).toFixed(2);
}

async function saveProfileData(showMessage) {
    if (showMessage) {
        profileDataMessage.textContent = '';
    }

    const body = new URLSearchParams({
        age: document.getElementById('profileAge').value.trim(),
        height_cm: document.getElementById('profileHeightCm').value.trim(),
        weight_kg: document.getElementById('profileWeightKg').value.trim(),
        goal_preference: document.getElementById('profileGoal').value
    });

    try {
        const response = await fetch('index.php?route=profile/update-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            if (showMessage) {
                profileDataMessage.className = 'profile-message error-text';
                profileDataMessage.textContent = data.message || 'Unable to save profile data.';
            }
            return;
        }

        if (showMessage) {
            profileDataMessage.className = 'profile-message success-text';
            profileDataMessage.textContent = data.message || 'Profile updated.';
        }

        if (data.user) {
            const bmiElement = document.getElementById('profileBmiValue');
            if (bmiElement) {
                bmiElement.textContent = data.user.bmi !== null && data.user.bmi !== undefined ? data.user.bmi : '-';
            }
        }
    } catch (error) {
        if (showMessage) {
            profileDataMessage.className = 'profile-message error-text';
            profileDataMessage.textContent = 'Server error while saving profile data.';
        }
    }
}

function fillProfileFromSavedCalculator() {
    const ageField = document.getElementById('profileAge');
    const heightField = document.getElementById('profileHeightCm');
    const weightField = document.getElementById('profileWeightKg');
    const goalField = document.getElementById('profileGoal');
    const savedValues = getSavedProfileValues();

    if (!savedValues || !goalField.value) {
        return;
    }

    if (!ageField.value) {
        ageField.value = savedValues.age;
    }
    if (!heightField.value) {
        heightField.value = savedValues.height_cm;
    }
    if (!weightField.value) {
        weightField.value = savedValues.weight_kg;
    }

    updateBmiPreview(heightField.value, weightField.value);

    if (ageField.value && heightField.value && weightField.value) {
        saveProfileData(false);
    }
}

profileDataForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    saveProfileData(true);
});

profileLogoutBtn.addEventListener('click', async function () {
    try {
        await fetch('index.php?route=auth/logout', {
            method: 'POST'
        });
    } catch (error) {
    }

    window.location.href = 'index.php?route=home';
});

fillProfileFromSavedCalculator();
