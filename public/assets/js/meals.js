const mealsLogoutBtn = document.getElementById('mealsLogoutBtn');

if (mealsLogoutBtn) {
    mealsLogoutBtn.addEventListener('click', async function () {
        try {
            await fetch('index.php?route=auth/logout', {
                method: 'POST'
            });
        } catch (error) {
        }

        window.location.href = 'index.php?route=home';
    });
}
