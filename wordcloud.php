<!-- File 9 of 9: wordcloud.php - MASSIVE QR CODE SECTION -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Word Cloud - EduLite</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/qrcode.min.js"></script>
    <style>
        body {
            padding: 0;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .game-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .game-header {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .game-title {
            color: #667eea;
            font-size: 28px;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .user-badge {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .admin-badge {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .change-name-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            min-height: auto;
            min-width: auto;
        }
        
        /* Login Modal */
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            max-width: 350px;
            width: 90%;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .login-box h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .login-box input {
            width: 100%;
            max-width: none;
            margin: 10px 0;
        }
        
        .login-box button {
            width: 100%;
            margin: 10px 0;
        }
        
        /* Input Section */
        .input-section {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
            position: sticky;
            top: 10px;
            z-index: 100;
        }
        
        .input-section h2 {
            color: #667eea;
            margin: 0 0 15px 0;
            font-size: 20px;
            text-align: center;
        }
        
        .input-wrapper {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .input-wrapper input {
            flex: 1;
            min-width: 200px;
            font-size: 20px;
            padding: 20px 25px;
            margin: 0;
            border: 2px solid #667eea;
        }
        
        .input-wrapper button {
            font-size: 20px;
            padding: 20px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            white-space: nowrap;
        }
        
        /* Admin Controls */
        .admin-controls {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .admin-controls p {
            margin: 0 0 10px 0;
            color: #856404;
            font-weight: 600;
        }
        
        .admin-controls button {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        
        /* Word Cloud Display */
        .cloud-display {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 30px;
            min-height: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
        }
        
        .cloud-display h2 {
            color: #667eea;
            margin: 0 0 20px 0;
            font-size: 22px;
            text-align: center;
        }
        
        #cloud-container {
            min-height: 450px;
            background: #fafafa;
            border-radius: 10px;
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            gap: 15px;
            line-height: 1.8;
        }
        
        .cloud-word-wrapper {
            display: inline-block;
            position: relative;
            margin: 8px;
            opacity: 0;
            animation: fadeIn 0.3s ease forwards;
        }
        
        /* UNIFORM SIZE - All words same large size */
        .cloud-word {
            display: inline-block;
            padding: 12px 24px;
            background: rgba(74, 144, 226, 0.1);
            border-radius: 25px;
            border: 2px solid rgba(74, 144, 226, 0.3);
            cursor: default;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            font-size: 28px !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }
        
        .cloud-word:hover {
            transform: scale(1.05);
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
            background: rgba(74, 144, 226, 0.15) !important;
        }
        
        /* Small delete button */
        .delete-btn {
            position: absolute;
            top: -6px;
            left: -6px;
            width: 16px;
            height: 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            z-index: 20;
            padding: 0;
            line-height: 1;
            opacity: 0.7;
            transition: all 0.15s ease;
        }
        
        .delete-btn:hover {
            background: #c0392b;
            opacity: 1;
            transform: scale(1.2);
        }
        
        .delete-btn:active {
            transform: scale(0.9);
        }
        
        .admin-mode .delete-btn {
            display: flex;
        }
        
        /* Count badge - shows popularity */
        .count-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #e74c3c;
            color: white;
            font-size: 11px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        /* Hot indicator for very popular words */
        .hot-indicator {
            display: block;
            font-size: 16px;
            margin-bottom: 3px;
            text-align: center;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        @keyframes deleteAnim {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.5); opacity: 0.5; }
            100% { transform: scale(0); opacity: 0; }
        }
        
        .deleting {
            animation: deleteAnim 0.3s ease forwards;
        }
        
        /* MASSIVE QR Code Section */
        .qr-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 50px 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            margin-top: 30px;
            text-align: center;
        }
        
        .qr-section h3 {
            color: white;
            margin: 0 0 30px 0;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        
        #qrcode {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .qr-instructions {
            text-align: left;
            color: white;
            font-size: 18px;
            line-height: 1.8;
            max-width: 300px;
        }
        
        .qr-instructions ol {
            margin: 15px 0;
            padding-left: 25px;
            text-align: left;
        }
        
        .qr-instructions li {
            margin: 10px 0;
        }
        
        .qr-url {
            margin-top: 20px;
            font-weight: 700;
            color: #fff;
            background: rgba(255,255,255,0.2);
            padding: 15px 20px;
            border-radius: 10px;
            word-break: break-all;
            font-size: 16px;
            display: inline-block;
        }
        
        .qr-hint {
            color: rgba(255,255,255,0.9);
            font-size: 16px;
            margin-top: 20px;
            font-style: italic;
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #27ae60;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }
        
        @media (max-width: 480px) {
            .game-title { font-size: 22px; }
            .input-wrapper { flex-direction: column; }
            .input-wrapper input, .input-wrapper button { width: 100%; }
            .cloud-display { min-height: 350px; padding: 15px; }
            #cloud-container { min-height: 300px; padding: 15px; gap: 10px; }
            .qr-section { padding: 30px 20px; }
            .qr-section h3 { font-size: 24px; }
            .qr-container { flex-direction: column; text-align: center; gap: 30px; }
            .qr-instructions { text-align: center; max-width: none; font-size: 16px; }
            .qr-instructions ol { text-align: left; display: inline-block; }
            #qrcode { padding: 15px; }
            .cloud-word { font-size: 22px !important; padding: 10px 18px; }
            .delete-btn { width: 14px; height: 14px; font-size: 10px; }
            .count-badge { min-width: 18px; height: 18px; font-size: 10px; }
        }
        
        @media (max-height: 500px) and (orientation: landscape) {
            .game-container { padding: 10px; }
            .input-section { padding: 15px; margin-bottom: 10px; }
            .input-wrapper input { padding: 14px 20px; font-size: 18px; }
            .input-wrapper button { padding: 14px 30px; }
            .cloud-display { min-height: 250px; padding: 15px; }
            #cloud-container { min-height: 220px; padding: 15px; }
            .qr-section { display: none; }
        }
        
        @media (min-width: 1000px) {
            .cloud-display { min-height: 550px; }
            #cloud-container { min-height: 500px; }
        }
    </style>
</head>
<body>

<!-- Login Modal -->
<div id="login-modal" class="login-modal hidden">
    <div class="login-box">
        <h2>👋 Welcome!</h2>
        <p style="color: #666; margin-bottom: 20px;">Enter your name to join the word cloud</p>
        <input type="text" id="username-input" placeholder="Your name" autocomplete="name" autocapitalize="words">
        <button type="button" onclick="saveUsername()">Join Game</button>
    </div>
</div>

<div class="game-container">
    <!-- Header -->
    <div class="game-header">
        <h1 class="game-title">
            <span>☁️</span>
            <span>Word Cloud</span>
            <span class="live-indicator">
                <span class="live-dot"></span>
                LIVE
            </span>
        </h1>
        <div class="user-info">
            <span class="user-badge" id="user-badge">👤 Guest</span>
            <span class="admin-badge hidden" id="admin-badge">🔑 Admin</span>
            <button type="button" class="change-name-btn" onclick="showLogin()">Change Name</button>
            <button type="button" class="change-name-btn" onclick="goHome()">← Back to Menu</button>
        </div>
    </div>
    
    <!-- Admin Controls (Only visible to admin) -->
    <div class="admin-controls hidden" id="admin-controls">
        <p>⚠️ Admin Mode: Click ❌ on any word to delete it</p>
        <button type="button" onclick="toggleAdminMode()">Toggle Delete Mode</button>
        <button type="button" onclick="resetAllWords()">🗑️ Reset All Words</button>
    </div>
    
    <!-- Input Section -->
    <div class="input-section">
        <h2>✍️ Add Your Word</h2>
        <div class="input-wrapper">
            <input type="text" id="word-input" placeholder="Type a word and press Submit..." maxlength="50" autocomplete="off">
            <button type="button" onclick="submitWord()">🚀 Submit</button>
        </div>
    </div>
    
    <!-- Word Cloud Display -->
    <div class="cloud-display" id="cloud-display">
        <h2>📊 Live Word Cloud</h2>
        <div id="cloud-container">
            <p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">Loading...</p>
        </div>
    </div>
    
    <!-- MASSIVE QR Code Section -->
    <div class="qr-section">
        <h3>📱 Scan to Join!</h3>
        <div class="qr-container">
            <div id="qrcode"></div>
            <div class="qr-instructions">
                <h4 style="margin: 0 0 15px 0; font-size: 20px;">How to join:</h4>
                <ol>
                    <li>Open your phone camera</li>
                    <li>Scan the QR code</li>
                    <li>Enter your name</li>
                    <li>Start submitting words!</li>
                </ol>
                <div class="qr-url" id="qr-url"></div>
                <p class="qr-hint">✨ Words appear here in real-time!</p>
            </div>
        </div>
    </div>
</div>

<script>
    const API = 'api.php';
    let username = localStorage.getItem('eduUsername') || '';
    let isAdmin = false;
    let adminMode = false;
    let pollInterval = null;
    
    // Hardcoded QR URL for production
    const QR_URL = 'https://testingdomain.ru/edulite/wordcloud.php';
    
    const COLOR_PALETTE = [
        '#2c3e50', '#34495e', '#5d4e6d', '#4a5568', '#2d5d7c',
        '#6b4c7a', '#3d6b5f', '#7c524a', '#4a6b7c', '#5a4d7a'
    ];
    
    document.addEventListener('DOMContentLoaded', () => {
        // Generate MASSIVE QR code
        try {
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById("qrcode"), {
                    text: QR_URL,
                    width: 250,
                    height: 250,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            } else {
                document.getElementById('qrcode').innerHTML = 
                    '<div style="width:250px;height:250px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;text-align:center;font-size:14px;padding:20px;">QR library not loaded<br><br>' + QR_URL + '</div>';
            }
        } catch(e) {
            console.error('QR generation error:', e);
            document.getElementById('qrcode').innerHTML = 
                '<div style="width:250px;height:250px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;text-align:center;font-size:14px;padding:20px;">' + QR_URL + '</div>';
        }
        
        document.getElementById('qr-url').textContent = QR_URL;
        
        // Check login
        if (username) {
            document.getElementById('user-badge').textContent = '👤 ' + username;
            document.getElementById('login-modal').classList.add('hidden');
        } else {
            document.getElementById('login-modal').classList.remove('hidden');
        }
        
        // Check admin status
        checkAdminStatus();
        
        // Start word cloud
        renderCloud(true);
        pollInterval = setInterval(renderCloud, 5000);
        
        // Focus input
        setTimeout(() => {
            const input = document.getElementById('word-input');
            if (input) input.focus();
        }, 500);
    });
    
    // Back button
    function goHome() {
        window.location.replace('index.php');
    }
    
    function checkAdminStatus() {
        fetch(API + '?action=check_session')
            .then(r => r.json())
            .then(data => {
                isAdmin = data.is_admin || false;
                if (isAdmin) {
                    document.getElementById('admin-badge').classList.remove('hidden');
                    document.getElementById('admin-controls').classList.remove('hidden');
                }
            })
            .catch(err => console.error(err));
    }
    
    function toggleAdminMode() {
        adminMode = !adminMode;
        const cloudDisplay = document.getElementById('cloud-display');
        if (adminMode) {
            cloudDisplay.classList.add('admin-mode');
            if (navigator.vibrate) navigator.vibrate(100);
        } else {
            cloudDisplay.classList.remove('admin-mode');
        }
    }
    
    function resetAllWords() {
        if (confirm('⚠️ Delete ALL words? This cannot be undone!')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset&type=words'
            })
            .then(r => r.json())
            .then(() => {
                renderCloud(true);
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                alert('✅ All words deleted');
            })
            .catch(err => alert('Error: ' + err));
        }
    }
    
    function deleteWord(word) {
        if (confirm('Delete "' + word + '"?')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_word&word=' + encodeURIComponent(word)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCloud(true);
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Error: ' + err));
        }
    }
    
    function saveUsername() {
        const input = document.getElementById('username-input');
        if (input.value.trim()) {
            username = input.value.trim();
            localStorage.setItem('eduUsername', username);
            document.getElementById('user-badge').textContent = '👤 ' + username;
            document.getElementById('login-modal').classList.add('hidden');
            document.getElementById('word-input').focus();
        }
    }
    
    function showLogin() {
        document.getElementById('login-modal').classList.remove('hidden');
        document.getElementById('username-input').value = '';
        document.getElementById('username-input').focus();
    }
    
    function submitWord() {
        const input = document.getElementById('word-input');
        const word = input.value.trim();
        if (!word) {
            if (navigator.vibrate) navigator.vibrate(200);
            input.focus();
            return;
        }
        
        const btn = event.target;
        btn.textContent = 'Sending...';
        btn.disabled = true;
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=add_word&word=' + encodeURIComponent(word) + '&username=' + encodeURIComponent(username)
        })
        .then(r => r.json())
        .then(() => {
            input.value = '';
            renderCloud(true);
            if (navigator.vibrate) navigator.vibrate(100);
        })
        .catch(err => {
            console.error('Submit error:', err);
            alert('Could not submit. Check connection.');
        })
        .finally(() => {
            btn.textContent = '🚀 Submit';
            btn.disabled = false;
            input.focus();
        });
    }
    
    function renderCloud(force = false) {
        const timestamp = new Date().getTime();
        const cacheBuster = '&_t=' + (force ? timestamp : Math.floor(timestamp / 5000));
        
        fetch(API + '?action=get_words' + cacheBuster, {
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const container = document.getElementById('cloud-container');
            if (!container) return;
            container.innerHTML = '';
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">No words yet. Be the first! 👆</p>';
                return;
            }
            
            // Sort by count (most popular first)
            data.sort((a, b) => {
                const countA = a.count || 1;
                const countB = b.count || 1;
                if (countB !== countA) return countB - countA;
                return b.lastTime - a.lastTime;
            });
            
            const wordsToShow = data.slice(0, 80);
            
            wordsToShow.forEach((item, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'cloud-word-wrapper';
                wrapper.style.animationDelay = (index * 0.03) + 's';
                
                const text = item.display || item.word;
                const count = item.count || 1;
                
                // Get color from palette
                let hash = 0;
                for (let i = 0; i < text.length; i++) {
                    hash = text.charCodeAt(i) + ((hash << 5) - hash);
                }
                const color = COLOR_PALETTE[Math.abs(hash) % COLOR_PALETTE.length];
                
                const span = document.createElement('span');
                span.className = 'cloud-word';
                span.textContent = text;
                span.style.color = color;
                span.style.borderColor = color;
                span.style.cursor = 'default';
                span.title = count + ' submissions';
                
                // Delete button (admin only)
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-btn';
                deleteBtn.textContent = '×';
                deleteBtn.title = 'Delete this word';
                deleteBtn.onclick = (e) => {
                    e.stopPropagation();
                    deleteWord(item.word);
                };
                
                // Count badge
                const badge = document.createElement('span');
                badge.className = 'count-badge';
                badge.textContent = count;
                
                // Hot indicator
                if (count >= 10) {
                    const hot = document.createElement('span');
                    hot.className = 'hot-indicator';
                    hot.textContent = '🔥';
                    wrapper.appendChild(hot);
                }
                
                wrapper.appendChild(deleteBtn);
                wrapper.appendChild(span);
                wrapper.appendChild(badge);
                container.appendChild(wrapper);
            });
            
            const cloudDisplay = document.getElementById('cloud-display');
            if (adminMode && isAdmin) {
                cloudDisplay.classList.add('admin-mode');
            }
        })
        .catch(err => {
            console.error('Render cloud error:', err);
        });
    }
    
    // Enter key to submit
    document.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const modal = document.getElementById('login-modal');
            if (!modal.classList.contains('hidden')) {
                saveUsername();
            } else {
                submitWord();
            }
        }
    });
</script>

</body>
</html>