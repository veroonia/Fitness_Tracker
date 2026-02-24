const profileDataForm = document.getElementById('profileDataForm');
const profileDataMessage = document.getElementById('profileDataMessage');
const profileLogoutBtn = document.getElementById('profileLogoutBtn');

profileDataForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    profileDataMessage.textContent = '';

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
            profileDataMessage.className = 'profile-message error-text';
            profileDataMessage.textContent = data.message || 'Unable to save profile data.';
            return;
        }

        profileDataMessage.className = 'profile-message success-text';
        profileDataMessage.textContent = data.message || 'Profile updated.';

        if (data.user) {
            const bmiElement = document.getElementById('profileBmiValue');
            if (bmiElement) {
                bmiElement.textContent = data.user.bmi !== null && data.user.bmi !== undefined ? data.user.bmi : '-';
            }
        }
    } catch (error) {
        profileDataMessage.className = 'profile-message error-text';
        profileDataMessage.textContent = 'Server error while saving profile data.';
    }
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
