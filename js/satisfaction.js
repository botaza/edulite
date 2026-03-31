// File 7 of 8: js/satisfaction.js - MOBILE OPTIMIZED
const Satisfaction = {
    lastVoteTime: 0,

    init() {
        App.showScreen('screen-satisfaction');
        this.updateStats();
        App.pollInterval = setInterval(() => this.updateStats(), 10000);
    },

    submit(rating) {
        // Prevent rapid taps
        const now = Date.now();
        if (now - this.lastVoteTime < 1000) return;
        this.lastVoteTime = now;
        
        // Haptic feedback based on rating
        if (navigator.vibrate) {
            if (rating >= 4) navigator.vibrate([50, 50, 50]); // Happy vibration
            else if (rating <= 2) navigator.vibrate(200); // Sad vibration
            else navigator.vibrate(100);
        }
        
        App.fetch('', 'POST', `action=vote&rating=${rating}`)
            .then(res => {
                if (res.success) {
                    this.updateStats();
                    // Visual feedback
                    const btn = event.target;
                    btn.style.background = '#d4edda';
                    setTimeout(() => btn.style.background = '', 500);
                } else {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    alert(res.message);
                }
            })
            .catch(err => {
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                alert('Could not submit. Check connection.');
            });
    },

    updateStats() {
        App.fetch('?action=get_stats')
            .then(data => {
                const scoreEl = document.getElementById('avg-score');
                if (scoreEl) {
                    // Animate the number change
                    const current = parseFloat(scoreEl.textContent) || 0;
                    const target = data.average;
                    scoreEl.textContent = target;
                    
                    // Color based on score
                    if (target >= 4) scoreEl.style.color = '#27ae60';
                    else if (target >= 3) scoreEl.style.color = '#f39c12';
                    else scoreEl.style.color = '#e74c3c';
                }
            })
            .catch(err => console.error('Satisfaction stats error:', err));
    }
};