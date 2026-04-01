<!-- File 9 of 8: wordcloud.php - COMPLETE WITH LOGIN-BASED USER COUNT -->
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
            padding: 0; margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .game-container { max-width: 1200px; margin: 0 auto; padding: 15px; }
        .game-header {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .game-title {
            color: #667eea; font-size: 28px; margin: 0 0 15px 0;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            flex-wrap: wrap;
        }
        .user-info {
            display: flex; justify-content: center; align-items: center;
            gap: 15px; flex-wrap: wrap;
        }
        .user-badge, .admin-badge {
            background: #667eea; color: white; padding: 8px 16px;
            border-radius: 20px; font-weight: 600; font-size: 14px;
        }
        .admin-badge { background: #e74c3c; }
        .change-name-btn {
            background: #95a5a6; color: white; border: none;
            padding: 8px 15px; border-radius: 20px; cursor: pointer;
            font-size: 13px; min-height: auto; min-width: auto;
        }
        .login-modal {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8); display: flex;
            align-items: center; justify-content: center; z-index: 1000;
        }
        .login-box {
            background: white; padding: 30px; border-radius: 15px;
            text-align: center; max-width: 350px; width: 90%;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .login-box h2 { color: #667eea; margin-bottom: 20px; }
        .login-box input { width: 100%; max-width: none; margin: 10px 0; }
        .login-box button { width: 100%; margin: 10px 0; }
        .input-section {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px; position: sticky; top: 10px; z-index: 100;
        }
        .input-section h2 {
            color: #667eea; margin: 0 0 15px 0; font-size: 20px; text-align: center;
        }
        .input-wrapper { display: flex; gap: 10px; flex-wrap: wrap; }
        .input-wrapper input {
            flex: 1; min-width: 200px; font-size: 20px;
            padding: 20px 25px; margin: 0; border: 2px solid #667eea;
        }
        .input-wrapper button {
            font-size: 20px; padding: 20px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            white-space: nowrap;
        }
        .admin-controls {
            background: #fff3cd; border: 2px solid #ffc107;
            border-radius: 10px; padding: 15px; margin-bottom: 15px; text-align: center;
        }
        .admin-controls p { margin: 0 0 10px 0; color: #856404; font-weight: 600; }
        .admin-controls button {
            background: #e74c3c; color: white; border: none;
            padding: 10px 20px; border-radius: 8px; cursor: pointer;
            font-size: 14px; margin: 5px;
        }
        .admin-controls button.active { background: #27ae60; }
        .cloud-display {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 30px; min-height: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
        }
        .cloud-display h2 {
            color: #667eea; margin: 0 0 20px 0; font-size: 22px; text-align: center;
        }
        #cloud-container {
            min-height: 450px; background: #fafafa; border-radius: 10px;
            padding: 30px; display: flex; flex-wrap: wrap;
            justify-content: center; align-content: center; gap: 15px; line-height: 1.8;
        }
        .cloud-word-wrapper {
            display: inline-block; position: relative; margin: 8px;
            opacity: 0; animation: fadeIn 0.3s ease forwards;
        }
        .cloud-word {
            display: inline-block; padding: 12px 24px;
            background: rgba(74, 144, 226, 0.1); border-radius: 25px;
            border: 2px solid rgba(74, 144, 226, 0.3); cursor: default;
            transition: all 0.2s ease; white-space: nowrap; position: relative;
            font-size: 28px !important; font-weight: 600 !important; box-shadow: none !important;
        }
        .cloud-word:hover {
            transform: scale(1.05); z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
            background: rgba(74, 144, 226, 0.15) !important;
        }
        .delete-btn {
            position: absolute; top: -6px; left: -6px; width: 16px; height: 16px;
            background: #e74c3c; color: white; border: none; border-radius: 50%;
            cursor: pointer; font-size: 12px; font-weight: bold; display: none;
            align-items: center; justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3); z-index: 20;
            padding: 0; line-height: 1; opacity: 0.7; transition: all 0.15s ease;
        }
        .delete-btn:hover { background: #c0392b; opacity: 1; transform: scale(1.2); }
        .delete-btn:active { transform: scale(0.9); }
        .admin-mode .delete-btn { display: flex; }
        .count-badge {
            position: absolute; top: -6px; right: -6px; background: #e74c3c;
            color: white; font-size: 11px; font-weight: 700; min-width: 20px;
            height: 20px; line-height: 20px; text-align: center;
            border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .user-indicator {
            position: absolute; bottom: -8px; right: 50%; transform: translateX(50%);
            background: #3498db; color: white; font-size: 10px; font-weight: 600;
            padding: 2px 8px; border-radius: 10px; display: none;
            white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .show-usernames .user-indicator { display: block; }
        .username-tooltip {
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
            background: #2c3e50; color: white; padding: 10px 15px; border-radius: 8px;
            font-size: 12px; max-width: 250px; text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); display: none;
            z-index: 100; margin-bottom: 10px; pointer-events: none;
        }
        .show-usernames .cloud-word-wrapper:hover .username-tooltip { display: block; }
        .username-tooltip::after {
            content: ''; position: absolute; top: 100%; left: 50%;
            transform: translateX(-50%); border: 6px solid transparent;
            border-top-color: #2c3e50;
        }
        .username-tooltip h4 {
            margin: 0 0 8px 0; font-size: 11px; color: #bdc3c7; text-transform: uppercase;
        }
        .username-tooltip .names {
            margin: 0; line-height: 1.6; max-height: 150px; overflow-y: auto;
        }
        .username-tooltip .names span {
            display: inline-block; background: rgba(255,255,255,0.1);
            padding: 2px 6px; border-radius: 4px; margin: 2px;
        }
        .hot-indicator {
            display: block; font-size: 16px; margin-bottom: 3px;
            text-align: center; animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        /* EMOJI METER STYLES */
        .emoji-meter-section {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px; text-align: center;
        }
        .emoji-meter-section h2 {
            color: #667eea; margin: 0 0 20px 0; font-size: 22px;
        }
        .emoji-buttons {
            display: flex; justify-content: center; gap: 15px;
            flex-wrap: wrap; margin-bottom: 20px;
        }
        .emoji-btn {
            background: white; border: 3px solid #ddd; border-radius: 15px;
            padding: 15px 25px; cursor: pointer; transition: all 0.2s;
            min-width: 100px; min-height: 80px; display: flex;
            flex-direction: column; align-items: center; justify-content: center;
            gap: 8px;
        }
        .emoji-btn:hover {
            transform: scale(1.05); border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .emoji-btn:active { transform: scale(0.95); }
        .emoji-btn.disabled {
            opacity: 0.5; cursor: not-allowed; filter: grayscale(100%);
        }
        .emoji-btn .emoji-icon { font-size: 40px; }
        .emoji-btn .emoji-label {
            font-size: 13px; font-weight: 600; color: #666;
        }
        .emoji-btn .cooldown {
            font-size: 11px; color: #e74c3c; font-weight: 600;
        }
        .emoji-stats {
            display: flex; justify-content: center; gap: 20px;
            flex-wrap: wrap; margin-top: 20px; padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .emoji-stat {
            background: #f8f9fa; padding: 10px 20px; border-radius: 10px;
            min-width: 80px; text-align: center;
        }
        .emoji-stat .stat-emoji { font-size: 24px; margin-bottom: 5px; }
        .emoji-stat .stat-count {
            font-size: 20px; font-weight: 700; color: #667eea;
        }
        .emoji-stat .stat-label {
            font-size: 11px; color: #999; text-transform: uppercase;
        }
        .admin-emoji-stats {
            background: #fff3cd; border: 2px solid #ffc107;
            border-radius: 10px; padding: 15px; margin-bottom: 15px;
            text-align: center;
        }
        .admin-emoji-stats h3 {
            color: #856404; margin: 0 0 15px 0; font-size: 16px;
        }
        .lap-info {
            display: inline-block; background: #667eea; color: white;
            padding: 5px 15px; border-radius: 15px; font-size: 13px;
            font-weight: 600; margin: 5px;
        }
        
        /* USER COUNT INDICATOR */
        .user-count-indicator {
            background: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            animation: pulse 2s infinite;
        }
        
        /* EMOJI ANIMATION OVERLAY */
        .emoji-animation-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none; z-index: 9999; display: none;
        }
        .emoji-animation-overlay.active { display: block; }
        .floating-emoji {
            position: absolute; font-size: 80px; animation: floatUp 3s ease-out forwards;
            opacity: 0;
        }
        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0.5); opacity: 1; }
            50% { opacity: 1; }
            100% { transform: translateY(-100px) scale(1.5); opacity: 0; }
        }
        
        /* EMOJI LOG SECTION */
        .emoji-log-section {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-top: 30px; text-align: left;
        }
        .emoji-log-section h3 {
            color: #667eea; margin: 0 0 20px 0; font-size: 20px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .emoji-log-section h3 button {
            background: #667eea; color: white; border: none;
            padding: 8px 15px; border-radius: 6px; cursor: pointer;
            font-size: 12px;
        }
        .emoji-log-table {
            width: 100%; border-collapse: collapse; font-size: 14px;
        }
        .emoji-log-table th {
            background: #667eea; color: white; padding: 12px;
            text-align: left; font-weight: 600;
        }
        .emoji-log-table th:first-child { border-radius: 8px 0 0 0; }
        .emoji-log-table th:last-child { border-radius: 0 8px 0 0; }
        .emoji-log-table td {
            padding: 10px 12px; border-bottom: 1px solid #eee;
        }
        .emoji-log-table tr:hover { background: #f8f9fa; }
        .emoji-log-table .col-emoji { font-size: 24px; width: 60px; text-align: center; }
        .emoji-log-table .col-user { font-weight: 600; color: #333; }
        .emoji-log-table .col-time { color: #999; font-size: 12px; }
        .emoji-log-table .col-lap {
            background: #667eea; color: white; padding: 3px 10px;
            border-radius: 12px; font-size: 11px; font-weight: 600;
            display: inline-block;
        }
        .emoji-log-table .col-action { width: 60px; text-align: center; }
        .emoji-log-table .delete-row-btn {
            background: #e74c3c; color: white; border: none;
            padding: 4px 10px; border-radius: 4px; cursor: pointer;
            font-size: 11px;
        }
        .no-log {
            text-align: center; color: #999; padding: 30px; font-style: italic;
        }
        
        .qr-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px; padding: 50px 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3); margin-top: 30px; text-align: center;
        }
        .qr-section h3 {
            color: white; margin: 0 0 30px 0; font-size: 32px;
            font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .qr-container {
            display: flex; justify-content: center; align-items: center;
            gap: 40px; flex-wrap: wrap;
        }
        #qrcode {
            background: white; padding: 20px; border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .qr-instructions {
            text-align: left; color: white; font-size: 18px;
            line-height: 1.8; max-width: 300px;
        }
        .qr-instructions ol { margin: 15px 0; padding-left: 25px; text-align: left; }
        .qr-instructions li { margin: 10px 0; }
        .qr-url {
            margin-top: 20px; font-weight: 700; color: #fff;
            background: rgba(255,255,255,0.2); padding: 15px 20px;
            border-radius: 10px; word-break: break-all; font-size: 16px;
            display: inline-block;
        }
        .qr-hint {
            color: rgba(255,255,255,0.9); font-size: 16px;
            margin-top: 20px; font-style: italic;
        }
        .live-indicator {
            display: inline-flex; align-items: center; gap: 6px;
            background: #27ae60; color: white; padding: 5px 12px;
            border-radius: 15px; font-size: 12px; font-weight: 600;
        }
        .live-dot {
            width: 8px; height: 8px; background: white;
            border-radius: 50%; animation: pulse 1s infinite;
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
            .username-tooltip { max-width: 200px; font-size: 11px; }
            .emoji-btn { min-width: 80px; min-height: 70px; padding: 12px 20px; }
            .emoji-btn .emoji-icon { font-size: 32px; }
            .emoji-btn .emoji-label { font-size: 11px; }
            .emoji-stats { gap: 10px; }
            .emoji-stat { min-width: 60px; padding: 8px 12px; }
            .emoji-log-section h3 { flex-direction: column; align-items: stretch; }
            .emoji-log-section h3 div { display: flex; gap: 8px; }
            .user-count-indicator {
                font-size: 11px;
                padding: 4px 10px;
                margin-left: 5px;
            }
        }
        
        @media (max-height: 500px) and (orientation: landscape) {
            .game-container { padding: 10px; }
            .input-section { padding: 15px; margin-bottom: 10px; }
            .input-wrapper input { padding: 14px 20px; font-size: 18px; }
            .input-wrapper button { padding: 14px 30px; }
            .cloud-display { min-height: 250px; padding: 15px; }
            #cloud-container { min-height: 220px; padding: 15px; }
            .emoji-meter-section { padding: 20px; }
            .emoji-buttons { gap: 10px; }
            .emoji-btn { min-width: 70px; min-height: 60px; padding: 10px 15px; }
            .emoji-btn .emoji-icon { font-size: 28px; }
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
        <p style="color: #666; margin-bottom: 20px;">Enter your name to join</p>
        <input type="text" id="username-input" placeholder="Your name" autocomplete="name" autocapitalize="words">
        <button type="button" onclick="saveUsername()">Join</button>
    </div>
</div>

<!-- Emoji Animation Overlay -->
<div class="emoji-animation-overlay" id="emoji-overlay"></div>

<div class="game-container">
    <!-- Header -->
    <div class="game-header">
        <h1 class="game-title">
            <span>☁️</span><span>Word Cloud</span>
            <span class="live-indicator">
                <span class="live-dot"></span>LIVE
            </span>
            <span class="user-count-indicator" id="user-count-display">
                👥 0 users
            </span>
        </h1>
        <div class="user-info">
            <span class="user-badge" id="user-badge">👤 Guest</span>
            <span class="admin-badge hidden" id="admin-badge">🔑 Admin</span>
            <button type="button" class="change-name-btn" onclick="showLogin()">Change Name</button>
            <button type="button" class="change-name-btn" onclick="goHome()">← Back</button>
        </div>
    </div>
    
    <!-- Admin Controls -->
    <div class="admin-controls hidden" id="admin-controls">
        <p>⚠️ Admin Mode</p>
        <button type="button" onclick="toggleAdminMode()" id="btn-delete-mode">🗑️ Delete</button>
        <button type="button" onclick="toggleUsernames()" id="btn-usernames">👥 Users</button>
        <button type="button" onclick="toggleEmojiStats()" id="btn-emoji-stats">📊 Emoji Stats</button>
        <button type="button" onclick="newLap()" id="btn-new-lap">🏁 New Lap</button>
        <button type="button" onclick="resetEmoji('all')" id="btn-reset-emoji">🔄 Reset Emoji</button>
        <button type="button" onclick="resetWords()" id="btn-reset-words">🗑️ Reset Words</button>
        <button type="button" onclick="toggleEmojiLog()" id="btn-emoji-log">📋 Emoji Log</button>
    </div>
    
    <!-- Admin Emoji Stats Panel -->
    <div class="admin-emoji-stats hidden" id="admin-emoji-stats">
        <h3>📊 Emoji Statistics (All-Time)</h3>
        <div class="lap-info">Lap: <span id="lap-number">1</span></div>
        <div class="lap-info" style="background: #27ae60;">Total Votes: <span id="total-votes">0</span></div>
        <div class="emoji-stats" id="admin-emoji-display"></div>
    </div>
    
    <!-- Input Section -->
    <div class="input-section">
        <h2>✍️ Add Your Word</h2>
        <div class="input-wrapper">
            <input type="text" id="word-input" placeholder="Type a word..." maxlength="50" autocomplete="off">
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
    
    <!-- EMOJI METER Section -->
    <div class="emoji-meter-section">
        <h2>📱 How Are You Doing?</h2>
        <p style="color: #666; margin-bottom: 20px;">Tap once every 60 seconds</p>
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
                <span class="emoji-label">In Pain</span>
                <span class="cooldown hidden" id="cooldown-pain"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('happy')" id="btn-emoji-happy">
                <span class="emoji-icon">😊</span>
                <span class="emoji-label">Happy</span>
                <span class="cooldown hidden" id="cooldown-happy"></span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('help')" id="btn-emoji-help">
                <span class="emoji-icon">🙋</span>
                <span class="emoji-label">Need Help</span>
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
    
    <!-- EMOJI LOG Section (Admin Only) -->
    <div class="emoji-log-section hidden" id="emoji-log-section">
        <h3>
            <span>📋 Emoji Vote Log</span>
            <div>
                <button type="button" onclick="deleteEmojiLog('all')" style="background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; margin-right: 8px;">🗑️ Clear All</button>
                <button type="button" onclick="refreshEmojiLog()" style="background: #667eea; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px;">🔄 Refresh</button>
            </div>
        </h3>
        <table class="emoji-log-table" id="emoji-log-table">
            <thead>
                <tr>
                    <th>Emoji</th>
                    <th>User</th>
                    <th>Time</th>
                    <th>Lap</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="emoji-log-body">
                <tr><td colspan="5" class="no-log">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    
    <!-- QR Code Section -->
    <div class="qr-section">
        <h3>📱 Scan to Join!</h3>
        <div class="qr-container">
            <div id="qrcode"></div>
            <div class="qr-instructions">
                <h4 style="margin: 0 0 15px 0; font-size: 20px;">How to join:</h4>
                <ol>
                    <li>Open phone camera</li>
                    <li>Scan QR code</li>
                    <li>Enter your name</li>
                    <li>Start participating!</li>
                </ol>
                <div class="qr-url" id="qr-url"></div>
                <p class="qr-hint">✨ Real-time participation!</p>
            </div>
        </div>
    </div>
</div>

<script>
    const API = 'api.php';
    let username = localStorage.getItem('eduUsername') || '';
    let isAdmin = false;
    let adminMode = false;
    let showUsernamesMode = false;
    let showEmojiStatsMode = false;
    let showEmojiLogMode = false;
    let pollInterval = null;
    let emojiPollInterval = null;
    let lastVoteTime = 0;
    const QR_URL = 'https://testingdomain.ru/edulite/wordcloud.php';
    const COLOR_PALETTE = ['#2c3e50', '#34495e', '#5d4e6d', '#4a5568', '#2d5d7c', '#6b4c7a', '#3d6b5f', '#7c524a', '#4a6b7c', '#5a4d7a'];
    const EMOJI_MAP = {'done': '✅', 'unsure': '🤔', 'pain': '😰', 'happy': '😊', 'help': '🙋'};
    
    document.addEventListener('DOMContentLoaded', () => {
        // Generate QR code
        try {
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById("qrcode"), {
                    text: QR_URL, width: 250, height: 250,
                    colorDark: "#000000", colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } else {
                document.getElementById('qrcode').innerHTML = '<div style="width:250px;height:250px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;text-align:center;font-size:14px;padding:20px;">' + QR_URL + '</div>';
            }
        } catch(e) {
            document.getElementById('qrcode').innerHTML = '<div style="width:250px;height:250px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;text-align:center;font-size:14px;padding:20px;">' + QR_URL + '</div>';
        }
        document.getElementById('qr-url').textContent = QR_URL;
        
        if (username) {
            document.getElementById('user-badge').textContent = '👤 ' + username;
            document.getElementById('login-modal').classList.add('hidden');
            // Log user login (track by IP)
            logUserLogin(username);
        } else {
            document.getElementById('login-modal').classList.remove('hidden');
        }
        
        checkAdminStatus();
        renderCloud(true);
        pollInterval = setInterval(renderCloud, 5000);
        
        // Emoji stats polling
        updateEmojiStats();
        emojiPollInterval = setInterval(updateEmojiStats, 3000);
        
        // User count polling
        updateUserCount();
        setInterval(updateUserCount, 5000);
        
        // Check for emoji animations
        checkEmojiAnimation();
        setInterval(checkEmojiAnimation, 1000);
        
        setTimeout(() => {
            const input = document.getElementById('word-input');
            if (input) input.focus();
        }, 500);
    });
    
    function goHome() { window.location.replace('index.php'); }
    
    function checkAdminStatus() {
        fetch(API + '?action=check_session').then(r => r.json()).then(data => {
            isAdmin = data.is_admin || false;
            if (isAdmin) {
                document.getElementById('admin-badge').classList.remove('hidden');
                document.getElementById('admin-controls').classList.remove('hidden');
            }
        }).catch(err => console.error(err));
    }
    
    function toggleAdminMode() {
        adminMode = !adminMode;
        const cloudDisplay = document.getElementById('cloud-display');
        const btn = document.getElementById('btn-delete-mode');
        if (adminMode) {
            cloudDisplay.classList.add('admin-mode');
            btn.classList.add('active');
            btn.textContent = '✅ Delete ON';
            if (navigator.vibrate) navigator.vibrate(100);
        } else {
            cloudDisplay.classList.remove('admin-mode');
            btn.classList.remove('active');
            btn.textContent = '🗑️ Delete';
        }
    }
    
    function toggleUsernames() {
        showUsernamesMode = !showUsernamesMode;
        const cloudDisplay = document.getElementById('cloud-display');
        const btn = document.getElementById('btn-usernames');
        if (showUsernamesMode) {
            cloudDisplay.classList.add('show-usernames');
            btn.classList.add('active');
            btn.textContent = '👥 Users ON';
            if (navigator.vibrate) navigator.vibrate(100);
        } else {
            cloudDisplay.classList.remove('show-usernames');
            btn.classList.remove('active');
            btn.textContent = '👥 Users';
        }
        renderCloud(true);
    }
    
    function toggleEmojiStats() {
        showEmojiStatsMode = !showEmojiStatsMode;
        const panel = document.getElementById('admin-emoji-stats');
        const btn = document.getElementById('btn-emoji-stats');
        if (showEmojiStatsMode) {
            panel.classList.remove('hidden');
            btn.classList.add('active');
            btn.textContent = '📊 Stats ON';
            updateEmojiStats();
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('active');
            btn.textContent = '📊 Emoji Stats';
        }
    }
    
    function toggleEmojiLog() {
        showEmojiLogMode = !showEmojiLogMode;
        const section = document.getElementById('emoji-log-section');
        const btn = document.getElementById('btn-emoji-log');
        
        if (showEmojiLogMode) {
            section.classList.remove('hidden');
            btn.classList.add('active');
            btn.textContent = '📋 Log ON';
            refreshEmojiLog();
            if (window.emojiLogInterval) clearInterval(window.emojiLogInterval);
            window.emojiLogInterval = setInterval(refreshEmojiLog, 5000);
        } else {
            section.classList.add('hidden');
            btn.classList.remove('active');
            btn.textContent = '📋 Emoji Log';
            if (window.emojiLogInterval) clearInterval(window.emojiLogInterval);
        }
    }
    
    function deleteEmojiLog(type, index = -1) {
        if (type === 'all') {
            if (!confirm('⚠️ Delete ALL emoji vote history? This cannot be undone!')) {
                return;
            }
        } else {
            if (!confirm('Delete this vote entry?')) {
                return;
            }
        }
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_emoji_log&type=' + type + (index >= 0 ? '&index=' + index : '')
        }).then(r => r.json()).then(data => {
            if (data.success) {
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                refreshEmojiLog();
                updateEmojiStats();
            } else {
                alert('Error: ' + data.message);
            }
        }).catch(err => alert('Error: ' + err));
    }
    
    function refreshEmojiLog() {
        if (!showEmojiLogMode) return;
        
        fetch(API + '?action=get_emoji_log')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.log) {
                    const tbody = document.getElementById('emoji-log-body');
                    
                    if (data.log.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="no-log">No votes yet</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = data.log.map((entry, index) => {
                        const date = new Date(entry.time * 1000);
                        const timeStr = date.toLocaleTimeString();
                        const emoji = EMOJI_MAP[entry.emoji] || entry.emoji;
                        const lapNum = entry.lap || 1;
                        const userDisplay = entry.username || 'Anonymous';
                        
                        return '<tr>' +
                            '<td class="col-emoji">' + emoji + '</td>' +
                            '<td class="col-user">' + escapeHtml(userDisplay) + '</td>' +
                            '<td class="col-time">' + timeStr + '</td>' +
                            '<td><span class="col-lap">' + lapNum + '</span></td>' +
                            '<td class="col-action"><button type="button" class="delete-row-btn" onclick="deleteEmojiLog(\'single\', ' + index + ')">🗑️</button></td>' +
                            '</tr>';
                    }).join('');
                }
            })
            .catch(err => console.error('Log error:', err));
    }
    
    function newLap() {
        if (confirm('🏁 Start new lap? Current lap data will be separated but kept in totals.')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_emoji&type=lap'
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    alert('✅ New lap started! Lap #' + data.lap);
                    updateEmojiStats();
                }
            }).catch(err => alert('Error: ' + err));
        }
    }
    
    function resetEmoji(type) {
        if (confirm('⚠️ Delete ALL emoji data? This cannot be undone!')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_emoji&type=' + type
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    alert('✅ Emoji data reset');
                    updateEmojiStats();
                }
            }).catch(err => alert('Error: ' + err));
        }
    }
    
    function resetWords() {
        if (confirm('⚠️ Delete ALL words from word cloud? This cannot be undone!')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset&type=words'
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    alert('✅ Word cloud reset - all words deleted!');
                    renderCloud(true);
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            }).catch(err => alert('Error: ' + err));
        }
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
    
    function updateEmojiStats() {
        fetch(API + '?action=get_emoji_stats').then(r => r.json()).then(data => {
            if (data.success) {
                // Public stats show CURRENT LAP (for all users)
                const lap = data.currentLap;
                document.getElementById('stat-done').textContent = lap.done || 0;
                document.getElementById('stat-unsure').textContent = lap.unsure || 0;
                document.getElementById('stat-pain').textContent = lap.pain || 0;
                document.getElementById('stat-happy').textContent = lap.happy || 0;
                document.getElementById('stat-help').textContent = lap.help || 0;
                
                // Admin stats show ALL-TIME totals
                if (isAdmin && showEmojiStatsMode) {
                    const all = data.allTime;
                    document.getElementById('lap-number').textContent = data.lapNumber || 1;
                    document.getElementById('total-votes').textContent = all.total || 0;
                    
                    const display = document.getElementById('admin-emoji-display');
                    display.innerHTML = '';
                    ['done', 'unsure', 'pain', 'happy', 'help'].forEach(key => {
                        const div = document.createElement('div');
                        div.className = 'emoji-stat';
                        div.innerHTML = '<div class="stat-emoji">' + EMOJI_MAP[key] + '</div>' +
                            '<div class="stat-count">' + (all[key] || 0) + '</div>' +
                            '<div class="stat-label">' + key + ' (all-time)</div>';
                        display.appendChild(div);
                    });
                }
            }
        }).catch(err => console.error(err));
    }
    
    function updateUserCount() {
        fetch(API + '?action=get_user_count')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const userDisplay = document.getElementById('user-count-display');
                    if (userDisplay) {
                        userDisplay.textContent = '👥 ' + data.label;
                    }
                }
            })
            .catch(err => console.error('User count error:', err));
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
    
    function saveUsername() {
        const input = document.getElementById('username-input');
        if (input.value.trim()) {
            username = input.value.trim();
            localStorage.setItem('eduUsername', username);
            document.getElementById('user-badge').textContent = '👤 ' + username;
            document.getElementById('login-modal').classList.add('hidden');
            document.getElementById('word-input').focus();
            
            // Log user login (track by IP)
            logUserLogin(username);
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
        }).then(r => r.json()).then(() => {
            input.value = '';
            renderCloud(true);
            if (navigator.vibrate) navigator.vibrate(100);
        }).catch(err => alert('Error: ' + err)).finally(() => {
            btn.textContent = '🚀 Submit';
            btn.disabled = false;
            input.focus();
        });
    }
    
    function renderCloud(force = false) {
        const timestamp = new Date().getTime();
        const cacheBuster = '&_t=' + (force ? timestamp : Math.floor(timestamp / 5000));
        fetch(API + '?action=get_words' + cacheBuster, {
            headers: {'Cache-Control': 'no-cache', 'Pragma': 'no-cache', 'Expires': '0'}
        }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            const container = document.getElementById('cloud-container');
            if (!container) return;
            container.innerHTML = '';
            if (!data || data.length === 0) {
                container.innerHTML = '<p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">No words yet. Be first! 👆</p>';
                return;
            }
            data.sort((a, b) => {
                const countA = a.count || 1, countB = b.count || 1;
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
                const users = item.users || [];
                let hash = 0;
                for (let i = 0; i < text.length; i++) hash = text.charCodeAt(i) + ((hash << 5) - hash);
                const color = COLOR_PALETTE[Math.abs(hash) % COLOR_PALETTE.length];
                const span = document.createElement('span');
                span.className = 'cloud-word';
                span.textContent = text;
                span.style.color = color;
                span.style.borderColor = color;
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-btn';
                deleteBtn.textContent = '×';
                deleteBtn.onclick = (e) => { e.stopPropagation(); deleteWord(item.word); };
                const userIndicator = document.createElement('div');
                userIndicator.className = 'user-indicator';
                userIndicator.textContent = users.length + ' user' + (users.length > 1 ? 's' : '');
                const tooltip = document.createElement('div');
                tooltip.className = 'username-tooltip';
                let uniqueUsers = [...new Set(users)];
                tooltip.innerHTML = '<h4>' + uniqueUsers.length + ' User' + (uniqueUsers.length > 1 ? 's' : '') + '</h4><div class="names">' + uniqueUsers.map(u => '<span>' + escapeHtml(u) + '</span>').join('') + '</div>';
                const badge = document.createElement('span');
                badge.className = 'count-badge';
                badge.textContent = count;
                if (count >= 10) {
                    const hot = document.createElement('span');
                    hot.className = 'hot-indicator';
                    hot.textContent = '🔥';
                    wrapper.appendChild(hot);
                }
                wrapper.appendChild(deleteBtn);
                wrapper.appendChild(span);
                wrapper.appendChild(userIndicator);
                wrapper.appendChild(tooltip);
                wrapper.appendChild(badge);
                container.appendChild(wrapper);
            });
            const cloudDisplay = document.getElementById('cloud-display');
            if (adminMode && isAdmin) cloudDisplay.classList.add('admin-mode');
            if (showUsernamesMode && isAdmin) cloudDisplay.classList.add('show-usernames');
        }).catch(err => console.error(err));
    }
    
    function deleteWord(word) {
        if (confirm('Delete "' + word + '"?')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_word&word=' + encodeURIComponent(word)
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    renderCloud(true);
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                } else alert('Error: ' + data.message);
            }).catch(err => alert('Error: ' + err));
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    document.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const modal = document.getElementById('login-modal');
            if (!modal.classList.contains('hidden')) saveUsername();
            else submitWord();
        }
    });
</script>

</body>
</html>