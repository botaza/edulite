// File 5 of 8: js/app.js - PRODUCTION READY (NO HARDCODED LOCALHOST)
const App = {
    // FIXED: Use relative path - works on localhost AND production server
    API: 'api.php',
    
    username: localStorage.getItem('eduUsername') || '',
    pollInterval: null,

    init() {
        console.log('🎓 App initialized');
        console.log('📡 API URL:', window.location.origin + '/' + this.API);
        
        if (this.username) {
            document.getElementById('display-username').textContent = this.username;
            this.showScreen('screen-list');
        } else {
            this.showScreen('screen-login');
        }
    },

    showScreen(id) {
        console.log('📍 Showing screen:', id);
        document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
        document.getElementById(id).classList.remove('hidden');
        
        if (!id.includes('wordcloud') && !id.includes('satisfaction')) {
            clearInterval(this.pollInterval);
        }
        window.scrollTo(0, 0);
    },

    saveUsername() {
        const input = document.getElementById('username-input');
        if (input.value.trim()) {
            this.username = input.value.trim();
            localStorage.setItem('eduUsername', this.username);
            document.getElementById('display-username').textContent = this.username;
            this.showScreen('screen-list');
        } else {
            if (navigator.vibrate) navigator.vibrate(200);
            input.focus();
        }
    },

    logout() {
        localStorage.removeItem('eduUsername');
        location.reload();
    },

    loadTool(type) {
        if (navigator.vibrate) navigator.vibrate(50);
        if (type === 'wordcloud') {
            // Redirect to dedicated wordcloud page
            window.location.href = 'wordcloud.php';
        } else if (type === 'satisfaction') {
            Satisfaction.init();
        }
    },

    checkAdmin() {
        Admin.check();
    },

    // Unified fetch with error handling - uses relative path
    fetch(endpoint, method = 'GET', body = null) {
        // FIXED: Use relative path - works on any domain
        const url = endpoint ? this.API + endpoint : this.API;
        const options = {
            method,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        };
        if (body) options.body = body;
        
        console.log('📡 Fetching:', url);
        
        return fetch(url, options)
            .then(r => {
                if (!r.ok) throw new Error('Network error: ' + r.status);
                return r.json();
            })
            .catch(err => {
                console.error('❌ API Error:', err);
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                throw err;
            });
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());