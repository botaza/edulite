// File 5 of 8: js/app.js - MOBILE OPTIMIZED + LOCAL FIX
const App = {
    // PATCHED: Hardcoded API path for local testing
    // Change this to match your server if needed
    API: 'http://localhost:8000/api.php',
    
    username: localStorage.getItem('eduUsername') || '',
    pollInterval: null,

    init() {
        console.log('🎓 App initialized');
        console.log('📡 API URL:', this.API);
        
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
        
        // Stop polling when leaving tools
        if (!id.includes('wordcloud') && !id.includes('satisfaction')) {
            clearInterval(this.pollInterval);
        }

        // Scroll to top on screen change
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
            // Vibrate on error (mobile only)
            if (navigator.vibrate) navigator.vibrate(200);
            input.focus();
        }
    },

    logout() {
        localStorage.removeItem('eduUsername');
        location.reload();
    },

    loadTool(type) {
        // Haptic feedback on tool selection
        if (navigator.vibrate) navigator.vibrate(50);
        
        if (type === 'wordcloud') {
            WordCloud.init();
        } else if (type === 'satisfaction') {
            Satisfaction.init();
        }
    },

    checkAdmin() {
        Admin.check();
    },

    // Unified fetch with error handling
    fetch(endpoint, method = 'GET', body = null) {
        // PATCHED: Use hardcoded API URL
        const url = this.API;
        const options = {
            method,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        };
        if (body) options.body = body;
        
        // Add action to URL for GET requests
        const fullUrl = method === 'GET' && endpoint ? this.API + endpoint : this.API;
        
        console.log('📡 Fetching:', fullUrl, method, body);
        
        return fetch(fullUrl, options)
            .then(r => {
                console.log('📥 Response status:', r.status);
                if (!r.ok) throw new Error('Network error: ' + r.status);
                return r.json();
            })
            .then(data => {
                console.log('📦 Response data:', data);
                return data;
            })
            .catch(err => {
                console.error('❌ API Error:', err);
                // Show user-friendly error on mobile
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                throw err;
            });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());