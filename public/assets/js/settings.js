const addAccountForm = document.getElementById('addAccountForm');
const editAccountForm = document.getElementById('editAccountForm');
const deleteAccountForm = document.getElementById('deleteAccountForm');
const settingsLogoutBtn = document.getElementById('settingsLogoutBtn');

const addAccountMessage = document.getElementById('addAccountMessage');
const editAccountMessage = document.getElementById('editAccountMessage');
const deleteAccountMessage = document.getElementById('deleteAccountMessage');

function setMessage(element, message, isError) {
    element.className = isError ? 'profile-message error-text' : 'profile-message success-text';
    element.textContent = message;
}

addAccountForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    setMessage(addAccountMessage, '', false);

    const body = new URLSearchParams({
        username: document.getElementById('addUsername').value.trim(),
        email: document.getElementById('addEmail').value.trim(),
        password: document.getElementById('addPassword').value
    });

    try {
        const response = await fetch('index.php?route=profile/settings/add-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            setMessage(addAccountMessage, data.message || 'Unable to add account.', true);
            return;
        }

        addAccountForm.reset();
        setMessage(addAccountMessage, data.message || 'Account added.', false);
    } catch (error) {
        setMessage(addAccountMessage, 'Server error while adding account.', true);
    }
});

editAccountForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    setMessage(editAccountMessage, '', false);

    const body = new URLSearchParams({
        username: document.getElementById('editUsername').value.trim(),
        email: document.getElementById('editEmail').value.trim(),
        password: document.getElementById('editPassword').value
    });

    try {
        const response = await fetch('index.php?route=profile/settings/edit-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            setMessage(editAccountMessage, data.message || 'Unable to update account.', true);
            return;
        }

        setMessage(editAccountMessage, data.message || 'Account updated.', false);

        if (data.user) {
            const settingsName = document.getElementById('settingsName');
            const settingsEmail = document.getElementById('settingsEmail');
            settingsName.textContent = data.user.username || settingsName.textContent;
            settingsEmail.textContent = data.user.email || settingsEmail.textContent;
        }

        document.getElementById('editPassword').value = '';
    } catch (error) {
        setMessage(editAccountMessage, 'Server error while updating account.', true);
    }
});

deleteAccountForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    setMessage(deleteAccountMessage, '', false);

    const body = new URLSearchParams({
        confirm_delete: document.getElementById('confirmDelete').value.trim()
    });

    try {
        const response = await fetch('index.php?route=profile/settings/delete-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            setMessage(deleteAccountMessage, data.message || 'Unable to delete account.', true);
            return;
        }

        window.location.href = data.redirectTo || 'index.php?route=home';
    } catch (error) {
        setMessage(deleteAccountMessage, 'Server error while deleting account.', true);
    }
});

settingsLogoutBtn.addEventListener('click', async function () {
    try {
        await fetch('index.php?route=auth/logout', {
            method: 'POST'
        });
    } catch (error) {
    }

    window.location.href = 'index.php?route=home';
});
