<!-- File 10 of 8: lesson.php - PDF VIEWER WITH EMOJI METER -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Lesson Mode - EduLite</title>
    <script src="js/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            padding: 0; margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 15px;
            height: 100vh;
        }
        .pdf-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .pdf-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pdf-header h2 {
            margin: 0;
            font-size: 18px;
        }
        .pdf-viewer {
            flex: 1;
            background: #f5f5f5;
            overflow: auto;
        }
        .pdf-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .no-pdf {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            font-size: 18px;
            text-align: center;
            padding: 40px;
        }
        .emoji-section {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 20px;
            overflow-y: auto;
        }
        .emoji-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .emoji-header h3 {
            margin: 0;
            color: #667eea;
            font-size: 18px;
        }
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #27ae60;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .live-dot {
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .emoji-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .emoji-btn {
            background: white;
            border: 2px solid #ddd;
            border-radius: 12px;
            padding: 12px 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .emoji-btn:hover {
            transform: scale(1.05);
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .emoji-btn:active { transform: scale(0.95); }
        .emoji-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(100%);
        }
        .emoji-btn .emoji-icon { font-size: 32px; }
        .emoji-btn .emoji-label {
            font-size: 11px;
            font-weight: 600;
            color: #666;
        }
        .emoji-btn .cooldown {
            font-size: 10px;
            color: #e74c3c;
            font-weight: 700;
        }
        .emoji-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }
        .emoji-stat {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
        }
        .emoji-stat .stat-emoji { font-size: 20px; margin-bottom: 3px; }
        .emoji-stat .stat-count {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
        }
        .emoji-stat .stat-label {
            font-size: 9px;
            color: #999;
            text-transform: uppercase;
        }
        .user-count {
            background: #3498db;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        
        /* Emoji Animation Overlay */
        .emoji-animation-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none;
            z-index: 9999;
            display: none;
        }
        .emoji-animation-overlay.active { display: block; }
        .floating-emoji {
            position: absolute;
            font-size: 80px;
            animation: floatUp 3s ease-out forwards;
            opacity: 0;
        }
        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0.5); opacity: 1; }
            50% { opacity: 1; }
            100% { transform: translateY(-100px) scale(1.5); opacity: 0; }
        }
        
        /* Mobile Responsive */
        @media (max-width: 900px) {
            .container {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr auto;
                height: auto;
            }
            .pdf-section {
                min-height: 500px;
            }
            .emoji-section {
                position: sticky;
                top: 0;
            }
        }
        
        @media (max-width: 480px) {
            .emoji-buttons {
                grid-template-columns: repeat(5, 1fr);
            }
            .emoji-btn {
                padding: 10px 5px;
            }
            .emoji-btn .emoji-icon { font-size: 24px; }
            .emoji-btn .emoji-label { font-size: 9px; }
            .emoji-stats {
                grid-template-columns: repeat(5, 1fr);
            }
            .emoji-stat {
                padding: 5px;
            }
            .emoji-stat .stat-emoji { font-size: 16px; }
            .emoji-stat .stat-count { font-size: 14px; }
        }
    </style>
</head>
<body>

<!-- Emoji Animation Overlay -->
<div class="emoji-animation-overlay" id="emoji-overlay"></div>

<div class="container">
    <!-- PDF Section -->
    <div class="pdf-section">
        <div class="pdf-header">
            <h2 id="pdf-title">📄 Lesson Material</h2>
            <button class="back-btn" onclick="window.location.href='index.php'">← Back to Menu</button>
        </div>
        <div class="pdf-viewer" id="pdf-viewer">
            <div class="no-pdf">
                <div>
                    <p style="font-size: 48px; margin-bottom: 20px;">📄</p>
                    <p>No lesson material uploaded yet</p>
                    <p style="font-size: 14px; margin-top: 10px;">Admin needs to upload a PDF</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Emoji Meter Section -->
    <div class="emoji-section">
        <div class="emoji-header">
            <h3>📱 How Are You Doing?</h3>
            <div>
                <span class="user-count" id="user-count-display">👥 0</span>
                <span class="live-indicator">
                    <span class="live-dot"></span>LIVE
                </span>
            </div>
        </div>
        
        <p style="color: #666; font-size: 12px; margin-bottom: 15px; text-align: center;">Tap once every 60 seconds</p>
        
        <div class="emoji-buttons">
            <button type="button" class="emoji-btn" onclick="submitEmoji('done')" id="btn-emoji-done">
                <span class="emoji-icon">✅</span>
                <span class="emoji-label">Done</span>
                <span class="cooldown hidden" id="cooldown-done"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('unsure')" id="btn-emoji-unsure">
                <span class="emoji-icon">🤔</span>
                <span class="emoji-label">Unsure</span>
                <span class="cooldown hidden" id="cooldown-unsure"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('pain')" id="btn-emoji-pain">
                <span class="emoji-icon">😰</span>
                <span class="emoji-label">Pain</span>
                <span class="cooldown hidden" id="cooldown-pain"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('happy')" id="btn-emoji-happy">
                <span class="emoji-icon">😊</span>
                <span class="emoji-label">Happy</span>
                <span class="cooldown hidden" id="cooldown-happy"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('help')" id="btn-emoji-help">
                <span class="emoji-icon">🙋</span>
                <span class="emoji-label">Help</span>
                <span class="cooldown hidden" id="cooldown-help"></span>
            </button>
        </div>
        
        <div class="emoji-stats" id="public-emoji-stats">
            <div class="emoji-stat">
                <div class="stat-emoji">✅</div>
                <div class="stat-count" id="stat-done">0</div>
                <div class="stat-label">Done</div>
            </div>
            <div class="emoji-stat">
                <div class="stat-emoji">🤔</div>
                <div class="stat-count" id="stat-unsure">0</div>
                <div class="stat-label">Unsure</div>
            </div>
            <div class="emoji-stat">
                <div class="stat-emoji">😰</div>
                <div class="stat-count" id="stat-pain">0</div>
                <div class="stat-label">Pain</div>
            </div>
            <div class="emoji-stat">
                <div class="stat-emoji">😊</div>
                <div class="stat-count" id="stat-happy">0</div>
                <div class="stat-label">Happy</div>
            </div>
            <div class="emoji-stat">
                <div class="stat-emoji">🙋</div>
                <div class="stat-count" id="stat-help">0</div>
                <div class="stat-label">Help</div>
            </div>
        </div>
    </div>
</div>

<script>
    const API = 'api.php';
    let username = localStorage.getItem('eduUsername') || '';
    let lastVoteTime = 0;
    let emojiPollInterval = null;
    const EMOJI_MAP = {'done': '✅', 'unsure': '🤔', 'pain': '😰', 'happy': '😊', 'help': '🙋'};
    
    document.addEventListener('DOMContentLoaded', () => {
        // Check if user is logged in
        if (!username) {
            window.location.href = 'index.php';
            return;
        }
        
        // Load PDF
        loadPdf();
        
        // Log user login
        logUserLogin(username);
        
        // Emoji stats polling
        updateEmojiStats();
        emojiPollInterval = setInterval(updateEmojiStats, 3000);
        
        // User count polling
        updateUserCount();
        setInterval(updateUserCount, 5000);
        
        // Check for emoji animations
        checkEmojiAnimation();
        setInterval(checkEmojiAnimation, 1000);
    });
    
    function loadPdf() {
        fetch(API + '?action=get_pdf_info')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.hasPdf) {
                    const pdfUrl = 'data/' + data.filename;
                    const viewer = document.getElementById('pdf-viewer');
                    const title = document.getElementById('pdf-title');
                    
                    title.textContent = '📄 ' + (data.original || 'Lesson.pdf');
                    viewer.innerHTML = '<iframe src="' + pdfUrl + '#toolbar=0" type="application/pdf"></iframe>';
                }
            })
            .catch(err => console.error('PDF load error:', err));
    }
    
    function updateUserCount() {
        fetch(API + '?action=get_user_count')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const userDisplay = document.getElementById('user-count-display');
                    if (userDisplay) {
                        userDisplay.textContent = '👥 ' + data.count;
                    }
                }
            })
            .catch(err => console.error('User count error:', err));
    }
    
    function updateEmojiStats() {
        fetch(API + '?action=get_emoji_stats').then(r => r.json()).then(data => {
            if (data.success) {
                const lap = data.currentLap;
                document.getElementById('stat-done').textContent = lap.done || 0;
                document.getElementById('stat-unsure').textContent = lap.unsure || 0;
                document.getElementById('stat-pain').textContent = lap.pain || 0;
                document.getElementById('stat-happy').textContent = lap.happy || 0;
                document.getElementById('stat-help').textContent = lap.help || 0;
            }
        }).catch(err => console.error(err));
    }
    
    function checkEmojiAnimation() {
        fetch(API + '?action=get_emoji_animation').then(r => r.json()).then(data => {
            if (data.emoji) {
                showEmojiAnimation(data.emoji);
            }
        }).catch(err => console.error(err));
    }
    
    function showEmojiAnimation(emoji) {
        const overlay = document.getElementById('emoji-overlay');
        const symbol = EMOJI_MAP[emoji] || emoji;
        
        for (let i = 0; i < 5; i++) {
            setTimeout(() => {
                const emojiEl = document.createElement('div');
                emojiEl.className = 'floating-emoji';
                emojiEl.textContent = symbol;
                emojiEl.style.left = (Math.random() * 80 + 10) + '%';
                emojiEl.style.animationDelay = (Math.random() * 0.5) + 's';
                overlay.appendChild(emojiEl);
                
                setTimeout(() => emojiEl.remove(), 3000);
            }, i * 200);
        }
        
        overlay.classList.add('active');
        setTimeout(() => overlay.classList.remove('active'), 3500);
    }
    
    function logUserLogin(user) {
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=log_user_login&username=' + encodeURIComponent(user)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                updateUserCount();
            }
        }).catch(err => console.error('Login log error:', err));
    }
    
    function submitEmoji(emoji) {
        const now = Date.now();
        if (now - lastVoteTime < 60000) {
            const wait = Math.ceil((60000 - (now - lastVoteTime)) / 1000);
            alert('Please wait ' + wait + ' seconds');
            return;
        }
        
        const btn = document.getElementById('btn-emoji-' + emoji);
        btn.classList.add('disabled');
        btn.disabled = true;
        lastVoteTime = now;
        
        const cooldown = document.getElementById('cooldown-' + emoji);
        cooldown.classList.remove('hidden');
        let seconds = 60;
        cooldown.textContent = seconds + 's';
        const countdown = setInterval(() => {
            seconds--;
            cooldown.textContent = seconds + 's';
            if (seconds <= 0) {
                clearInterval(countdown);
                cooldown.classList.add('hidden');
                btn.classList.remove('disabled');
                btn.disabled = false;
            }
        }, 1000);
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=emoji_vote&emoji=' + emoji + '&username=' + encodeURIComponent(username)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                if (navigator.vibrate) navigator.vibrate(100);
                updateEmojiStats();
            } else {
                btn.classList.remove('disabled');
                btn.disabled = false;
                alert(data.message);
            }
        }).catch(err => {
            btn.classList.remove('disabled');
            btn.disabled = false;
            alert('Error: ' + err);
        });
    }
</script>

</body>
</html>