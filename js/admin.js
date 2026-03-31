// File 8 of 8: js/admin.js - MOBILE OPTIMIZED
const Admin = {
    check() {
        App.fetch('?action=check_session')
            .then(res => {
                if (res.is_admin) {
                    App.showScreen('screen-admin-dash');
                } else {
                    App.showScreen('screen-admin-login');
                }
            })
            .catch(err => alert('Connection error. Please try again.'));
    },

    login() {
        const pass = document.getElementById('admin-pass').value;
        if (!pass) {
            if (navigator.vibrate) navigator.vibrate(200);
            document.getElementById('admin-pass').focus();
            return;
        }
        
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = 'Checking...';
        
        App.fetch('', 'POST', `action=login&password=${pass}`)
            .then(res => {
                if (res.success) {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    App.showScreen('screen-admin-dash');
                    document.getElementById('admin-pass').value = '';
                } else {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                    alert('Wrong Password');
                    document.getElementById('admin-pass').value = '';
                    document.getElementById('admin-pass').focus();
                }
            })
            .catch(err => alert('Connection error. Please try again.'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Login';
            });
    },

    reset(type) {
        if (confirm('⚠️ This will delete all data for this tool. Continue?')) {
            App.fetch('', 'POST', `action=reset&type=${type}`)
                .then(() => {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    alert('✅ Reset Complete');
                })
                .catch(err => alert('Could not reset. Try again.'));
        }
    }
};