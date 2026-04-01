// File 5 of 8: js/app.js - CLEAN VERSION

const App = {
    API: 'api.php',
    username: localStorage.getItem('eduUsername') || '',

    init() {
        if (this.username) {
            const display = document.getElementById('display-username');
            if (display) display.textContent = this.username;
            this.showScreen('screen-list');
        } else {
            this.showScreen('screen-login');
        }
    },

    showScreen(id) {
        document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
        const screen = document.getElementById(id);
        if (screen) screen.classList.remove('hidden');
    },

    saveUsername() {
        const input = document.getElementById('username-input');
        if (input && input.value.trim()) {
            this.username = input.value.trim();
            localStorage.setItem('eduUsername', this.username);
            const display = document.getElementById('display-username');
            if (display) display.textContent = this.username;
            this.showScreen('screen-list');
        }
    },

    logout() {
        localStorage.removeItem('eduUsername');
        location.reload();
    },

    checkAdmin() {
        if (typeof Admin !== 'undefined' && typeof Admin.check === 'function') {
            Admin.check();
        }
    },

    fetch(endpoint, method = 'GET', body = null) {
        const url = this.API + endpoint;
        const options = {
            method,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        };
        if (body) options.body = body;
        
        return fetch(url, options)
            .then(r => r.json())
            .catch(err => {
                console.error('API Error:', err);
                return {success: false, error: err.message};
            });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());