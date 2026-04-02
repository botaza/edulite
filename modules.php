<!-- File 11 of 8: modules.php - DYNAMIC QR LINK -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Modules - EduLite</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
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
        
        .admin-panel {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px; display: none;
        }
        .admin-panel.visible { display: block; }
        .admin-panel h3 {
            color: #667eea; margin: 0 0 20px 0; font-size: 20px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .admin-card {
            background: #f8f9fa; border-radius: 10px;
            padding: 20px; border: 2px solid #e9ecef;
        }
        .admin-card h4 {
            color: #667eea; margin: 0 0 15px 0; font-size: 16px;
        }
        .admin-card button {
            width: 100%; margin: 5px 0; padding: 10px;
            border: none; border-radius: 6px;
            cursor: pointer; font-size: 13px; font-weight: 600;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        
        .lap-info {
            background: #667eea; color: white;
            padding: 10px 15px; border-radius: 8px;
            margin-bottom: 15px; text-align: center;
        }
        .lap-info .lap-number { font-size: 32px; font-weight: 700; }
        .lap-info .lap-label { font-size: 12px; opacity: 0.9; }
        
        .pdf-info {
            background: #f8f9fa; padding: 15px;
            border-radius: 8px; margin-bottom: 15px; text-align: center;
        }
        .pdf-info .pdf-name { font-weight: 600; color: #667eea; word-break: break-all; }
        .pdf-info .pdf-time { font-size: 12px; color: #999; margin-top: 5px; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        .stat-box {
            background: white; padding: 10px;
            border-radius: 8px; text-align: center;
            border: 2px solid #e9ecef;
        }
        .stat-box .stat-emoji { font-size: 24px; }
        .stat-box .stat-count { font-size: 20px; font-weight: 700; color: #667eea; }
        .stat-box .stat-label { font-size: 10px; color: #999; text-transform: uppercase; }
        
        /* EMOJI LOG - FIXED AT BOTTOM WHEN TOGGLED */
        .emoji-log-section {
            background: rgba(255,255,255,0.98);
            border-radius: 15px 15px 0 0;
            padding: 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 900;
            max-height: 40vh;
            overflow-y: auto;
            display: none;
            border-top: 3px solid #667eea;
        }
        .emoji-log-section.visible {
            display: block;
            animation: slideUpLog 0.3s ease;
        }
        @keyframes slideUpLog {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        .emoji-log-section h3 {
            color: #667eea;
            margin: 0 0 15px 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .emoji-log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .emoji-log-table th {
            background: #667eea;
            color: white;
            padding: 8px;
            text-align: left;
            position: sticky;
            top: 0;
        }
        .emoji-log-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
        }
        .emoji-log-table tr:hover { background: #f8f9fa; }
        .emoji-log-table .col-emoji { font-size: 18px; width: 40px; text-align: center; }
        .emoji-log-table .col-user { font-weight: 600; }
        .emoji-log-table .col-time { color: #999; font-size: 10px; }
        .emoji-log-table .col-lap { 
            background: #667eea; color: white; 
            padding: 2px 6px; border-radius: 8px; 
            font-size: 9px; font-weight: 600; display: inline-block;
        }
        .emoji-log-table .col-action { text-align: center; }
        .delete-row-btn {
            background: #e74c3c; color: white;
            border: none; padding: 3px 8px;
            border-radius: 4px; cursor: pointer; font-size: 10px;
        }
        
        /* MODULE SECTIONS */
        .module-section {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
        }
        .module-section h2 {
            color: #667eea; margin: 0 0 20px 0; font-size: 22px; text-align: center;
        }
        
        .input-section {
            background: rgba(255,255,255,0.95); border-radius: 15px;
            padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
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
        #cloud-container {
            min-height: 400px; background: #fafafa; border-radius: 10px;
            padding: 30px; display: flex; flex-wrap: wrap;
            justify-content: center; align-content: center; gap: 15px;
        }
        .cloud-word-wrapper {
            display: inline-block; position: relative; margin: 8px;
            opacity: 0; animation: fadeIn 0.3s ease forwards;
        }
        .cloud-word {
            display: inline-block; padding: 12px 24px;
            background: rgba(74, 144, 226, 0.1); border-radius: 25px;
            border: 2px solid rgba(74, 144, 226, 0.3);
            font-size: 24px; font-weight: 600; color: #2c3e50;
            cursor: default; transition: all 0.2s ease;
        }
        .cloud-word:hover {
            transform: scale(1.05); z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .delete-btn {
            position: absolute; top: -6px; left: -6px;
            width: 16px; height: 16px;
            background: #e74c3c; color: white;
            border: none; border-radius: 50%;
            cursor: pointer; font-size: 12px; font-weight: bold;
            display: none; align-items: center; justify-content: center;
            z-index: 20;
            opacity: 0.6;
        }
        .delete-btn:hover { opacity: 1; }
        .admin-mode .delete-btn { display: flex; }
        .count-badge {
            position: absolute; top: -6px; right: -6px;
            background: #e74c3c; color: white;
            font-size: 11px; font-weight: 700;
            min-width: 20px; height: 20px;
            line-height: 20px; text-align: center;
            border-radius: 10px;
        }
        .user-indicator {
            position: absolute; bottom: -8px; right: 50%;
            transform: translateX(50%);
            background: #3498db; color: white;
            font-size: 10px; font-weight: 600;
            padding: 2px 8px; border-radius: 10px;
            display: none; white-space: nowrap;
        }
        .show-usernames .user-indicator { display: block; }
        .username-tooltip {
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%);
            background: #2c3e50; color: white;
            padding: 10px 15px; border-radius: 8px;
            font-size: 12px; max-width: 250px;
            text-align: center; display: none;
            z-index: 100; margin-bottom: 10px;
        }
        .show-usernames .cloud-word-wrapper:hover .username-tooltip { display: block; }
        .username-tooltip .names {
            margin: 0; line-height: 1.6;
            max-height: 150px; overflow-y: auto;
        }
        .username-tooltip .names span {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            padding: 2px 6px; border-radius: 4px; margin: 2px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .pdf-viewer-container {
            height: 70vh;
            background: #525659;
            border-radius: 10px;
            overflow-y: scroll;
            position: relative;
        }
        .pdf-controls {
            background: #333;
            padding: 10px;
            display: flex;
            justify-content: center;
            gap: 15px;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .pdf-controls button {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
        }
        .pdf-controls span {
            color: white;
            font-size: 14px;
            min-width: 90px;
            text-align: center;
        }
        .pdf-pages-container {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }
        .pdf-page-canvas {
            display: block;
            margin: 0 auto 20px auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            max-width: 100%;
        }
        .page-number-indicator {
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        .no-pdf {
            display: flex; align-items: center; justify-content: center;
            height: 100%; color: #999; font-size: 18px;
            text-align: center; padding: 40px;
        }
        
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
        
        .user-count-indicator {
            background: #3498db; color: white;
            padding: 5px 12px; border-radius: 15px;
            font-size: 12px; font-weight: 600;
            margin-left: 10px;
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
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .emoji-animation-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none; z-index: 9999; display: none;
        }
        .emoji-animation-overlay.active { display: block; }
        .floating-emoji {
            position: absolute; font-size: 80px;
            animation: floatUp 3s ease-out forwards; opacity: 0;
        }
        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0.5); opacity: 1; }
            50% { opacity: 1; }
            100% { transform: translateY(-100px) scale(1.5); opacity: 0; }
        }
        
        .hidden { display: none !important; }
        
        /* QR Code Module Styles */
        .qr-section {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 15px;
            text-align: center;
        }
        .qr-section h2 {
            color: #667eea;
            margin: 0 0 20px 0;
            font-size: 22px;
        }
        #qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }
        #qr-code {
            width: 300px;
            height: 300px;
        }
        .qr-link {
            display: block;
            margin-top: 15px;
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
            word-break: break-all;
        }
        
        @media (max-width: 480px) {
            .game-title { font-size: 22px; }
            .input-wrapper { flex-direction: column; }
            .input-wrapper input, .input-wrapper button { width: 100%; }
            .emoji-btn { min-width: 80px; min-height: 70px; padding: 12px 20px; }
            .emoji-btn .emoji-icon { font-size: 32px; }
            .emoji-stats { gap: 10px; }
            .emoji-stat { min-width: 60px; padding: 8px 12px; }
            .admin-grid { grid-template-columns: 1fr; }
            #qr-code { width: 200px; height: 200px; }
            .emoji-log-section { max-height: 50vh; }
            .emoji-log-table { font-size: 11px; }
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
            <span>🎯</span>
            <span>EduLite Modules</span>
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
        <p>⚠️ Admin Mode: Quick Controls</p>
        <button type="button" onclick="toggleDeleteMode()" id="btn-delete-mode">🗑️ Delete</button>
        <button type="button" onclick="toggleUsernames()" id="btn-usernames">👥 Users</button>
        <button type="button" onclick="toggleAdminPanel()" id="btn-emoji-stats">📊 Stats</button>
        <button type="button" onclick="newLap()" id="btn-new-lap">🏁 New Lap</button>
        <button type="button" onclick="resetEmoji('all')" id="btn-reset-emoji">🔄 Reset Emoji</button>
        <button type="button" onclick="resetWords()" id="btn-reset-words">🗑️ Reset Words</button>
        <button type="button" onclick="toggleEmojiLog()" id="btn-emoji-log">📋 Emoji Log</button>
        <button type="button" onclick="toggleModule('wordcloud')" id="btn-module-wordcloud">☁️ Word Cloud</button>
        <button type="button" onclick="toggleModule('pdf_viewer')" id="btn-module-pdf">📄 PDF</button>
        <button type="button" onclick="uploadPdf()" id="btn-quick-upload-pdf" style="background: #27ae60;">📤 Upload PDF</button>
        <button type="button" onclick="viewPdf()" style="background: #3498db;">👁️ View PDF</button>
        <button type="button" onclick="toggleModule('emoji_meter')" id="btn-module-emoji">📱 Emoji</button>
        <button type="button" onclick="toggleModule('qr_link')" id="btn-module-qr">🔗 QR Link</button>
    </div>
    
    <!-- Admin Panel -->
    <div class="admin-panel hidden" id="admin-panel">
        <h3>
            <span>⚙️ Admin Dashboard</span>
            <button type="button" onclick="toggleAdminPanel()" style="background: #95a5a6; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px;">✕ Close</button>
        </h3>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h4>🏁 Lap Management</h4>
                <div class="lap-info">
                    <div class="lap-number" id="admin-lap-number">1</div>
                    <div class="lap-label">Current Lap</div>
                </div>
                <button class="btn-primary" onclick="newLap()">🏁 Start New Lap</button>
            </div>
            
            <div class="admin-card">
                <h4>📄 PDF Management</h4>
                <div class="pdf-info" id="admin-pdf-info">
                    <div class="pdf-name" id="admin-pdf-name">No PDF uploaded</div>
                    <div class="pdf-time" id="admin-pdf-time"></div>
                </div>
                <button class="btn-success" onclick="uploadPdf()">📤 Upload/Change PDF</button>
                <button type="button" class="btn-primary" onclick="viewPdf()">👁️ View Current PDF</button>
                <button class="btn-danger" onclick="deletePdf()">🗑️ Delete PDF</button>
            </div>
            
            <div class="admin-card">
                <h4>🔄 Reset Controls</h4>
                <button class="btn-danger" onclick="resetWords()">🗑️ Reset Word Cloud</button>
                <button class="btn-warning" onclick="resetEmoji('lap')">🔄 Reset Current Lap</button>
                <button class="btn-danger" onclick="resetEmoji('all')">⚠️ Reset All Emoji</button>
            </div>
            
            <div class="admin-card" id="emoji-stats-card">
                <h4>📊 Emoji Stats (All-Time)</h4>
                <div class="stats-grid" id="admin-emoji-stats">
                    <div class="stat-box">
                        <div class="stat-emoji">✅</div>
                        <div class="stat-count" id="admin-stat-done">0</div>
                        <div class="stat-label">Done</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-emoji">🤔</div>
                        <div class="stat-count" id="admin-stat-unsure">0</div>
                        <div class="stat-label">Unsure</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-emoji">😰</div>
                        <div class="stat-count" id="admin-stat-pain">0</div>
                        <div class="stat-label">Pain</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-emoji">😊</div>
                        <div class="stat-count" id="admin-stat-happy">0</div>
                        <div class="stat-label">Happy</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-emoji">🙋</div>
                        <div class="stat-count" id="admin-stat-help">0</div>
                        <div class="stat-label">Help</div>
                    </div>
                </div>
                <p style="font-size: 11px; color: #999; margin-top: 10px;">Total: <span id="admin-total-votes">0</span></p>
            </div>
        </div>
    </div>
    
    <!-- Word Cloud Module -->
    <div class="module-section hidden" id="module-wordcloud">
        <h2>☁️ Word Cloud</h2>
        <div class="input-section">
            <h2>✍️ Add Your Word</h2>
            <div class="input-wrapper">
                <input type="text" id="word-input" placeholder="Type a word..." maxlength="50" autocomplete="off">
                <button type="button" onclick="submitWord()">🚀 Submit</button>
            </div>
        </div>
        <div id="cloud-container">
            <p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">Loading...</p>
        </div>
    </div>
    
    <!-- PDF Viewer Module -->
    <div class="module-section hidden" id="module-pdf">
        <h2>📄 PDF Viewer (Infinite Scroll)</h2>
        <div class="pdf-viewer-container" id="pdf-viewer">
            <div class="no-pdf">
                <div>
                    <p style="font-size: 48px; margin-bottom: 20px;">📄</p>
                    <p>No lesson material uploaded yet</p>
                </div>
            </div>
        </div>
        <div class="page-number-indicator hidden" id="page-indicator">
            Page <span id="current-page">0</span> of <span id="total-pages">0</span>
        </div>
    </div>
    
    <!-- Emoji Meter Module -->
    <div class="module-section hidden" id="module-emoji">
        <h2>📱 How Are You Doing?</h2>
        <p style="color: #666; margin-bottom: 20px;">Tap once every 60 seconds</p>
        <div class="emoji-buttons">
            <button type="button" class="emoji-btn" onclick="submitEmoji('done')" id="btn-emoji-done">
                <span class="emoji-icon">✅</span>
                <span class="emoji-label">Done</span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('unsure')" id="btn-emoji-unsure">
                <span class="emoji-icon">🤔</span>
                <span class="emoji-label">Unsure</span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('pain')" id="btn-emoji-pain">
                <span class="emoji-icon">😰</span>
                <span class="emoji-label">Pain</span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('happy')" id="btn-emoji-happy">
                <span class="emoji-icon">😊</span>
                <span class="emoji-label">Happy</span>
            </button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('help')" id="btn-emoji-help">
                <span class="emoji-icon">🙋</span>
                <span class="emoji-label">Help</span>
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
    
    <!-- QR Link Module -->
    <div class="module-section hidden" id="module-qr">
        <h2>🔗 Join Link</h2>
        <div id="qr-container">
            <div id="qr-code"></div>
        </div>
        <a href="#" class="qr-link" id="qr-link-display" target="_blank">
            Loading...
        </a>
    </div>
</div>

<!-- Emoji Log Section - FIXED AT BOTTOM -->
<div class="emoji-log-section" id="emoji-log-section">
    <h3>
        <span>📋 Emoji Vote Log</span>
        <div>
            <button type="button" onclick="deleteEmojiLog('all')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; margin-right: 5px;">🗑️ Clear All</button>
            <button type="button" onclick="refreshEmojiLog()" style="background: #667eea; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">🔄 Refresh</button>
            <button type="button" onclick="toggleEmojiLog()" style="background: #95a5a6; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; margin-left: 5px;">✕ Close</button>
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
            <tr><td colspan="5" style="text-align: center; color: #999; padding: 20px;">Loading...</td></tr>
        </tbody>
    </table>
</div>

<script>
    // PDF.js setup
    const PDFJS_VERSION = '3.11.174';
    
    // DYNAMIC QR URL - Generated from current page location
    function getCurrentModuleUrl() {
        const protocol = window.location.protocol;
        const hostname = window.location.hostname;
        const pathname = window.location.pathname;
        const cleanPath = pathname.split('?')[0].split('#')[0];
        return protocol + '//' + hostname + cleanPath;
    }
    
    function initPDFJS() {
        return new Promise((resolve) => {
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/${PDFJS_VERSION}/pdf.worker.min.js`;
                console.log('PDF.js worker initialized');
                resolve();
            } else {
                setTimeout(() => initPDFJS().then(resolve), 100);
            }
        });
    }
    
    const API = 'api.php';
    let username = localStorage.getItem('eduUsername') || '';
    let isAdmin = false;
    let modulesConfig = {};
    let lastVoteTime = 0;
    let pollInterval = null;
    let deleteMode = false;
    let showUsernamesMode = false;
    let showEmojiLogMode = false;
    
    // PDF.js variables
    let pdfDoc = null;
    let scale = 1.0;
    let currentPdfFilename = '';
    let totalPages = 0;
    let renderedPages = {};
    let isScrolling = false;
    let scrollTimeout = null;
    let pdfIsLoaded = false;

    // Track the last known server-side PDF filename so the poll can detect changes
    // without touching the viewer if nothing changed.
    let lastKnownServerFilename = null;
    
    const COLOR_PALETTE = ['#2c3e50', '#34495e', '#5d4e6d', '#4a5568', '#2d5d7c', '#6b4c7a', '#3d6b5f', '#7c524a', '#4a6b7c', '#5a4d7a'];
    const EMOJI_MAP = {'done': '✅', 'unsure': '🤔', 'pain': '😰', 'happy': '😊', 'help': '🙋'};
    
    document.addEventListener('DOMContentLoaded', () => {
        initPDFJS().then(() => console.log('PDF.js ready'));
        
        if (username) {
            document.getElementById('user-badge').textContent = '👤 ' + username;
            logUserLogin(username);
        } else {
            document.getElementById('login-modal').classList.remove('hidden');
        }
        
        checkAdminStatus();
        loadModulesConfig();
        
        pollInterval = setInterval(loadModulesConfig, 5000);
        setInterval(updateEmojiStats, 3000);
        setInterval(updateUserCount, 5000);
        setInterval(checkEmojiAnimation, 1000);
        
        window.addEventListener('beforeunload', savePdfPosition);
        window.addEventListener('pagehide', savePdfPosition);
        
        setTimeout(() => {
            const input = document.getElementById('word-input');
            if (input) input.focus();
        }, 500);
    });
    
    function goHome() { window.location.replace('index.php'); }
    
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
    
    function loadModulesConfig() {
        fetch(API + '?action=get_modules_config')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const serverConfig = data.config;
                    
                    modulesConfig = {
                        wordcloud:   serverConfig.wordcloud   !== undefined ? serverConfig.wordcloud   : (modulesConfig.wordcloud   || false),
                        pdf_viewer:  serverConfig.pdf_viewer  !== undefined ? serverConfig.pdf_viewer  : (modulesConfig.pdf_viewer  || false),
                        emoji_meter: serverConfig.emoji_meter !== undefined ? serverConfig.emoji_meter : (modulesConfig.emoji_meter || false),
                        qr_link:     serverConfig.qr_link     !== undefined ? serverConfig.qr_link     : (modulesConfig.qr_link     || false)
                    };
                    
                    renderModules();
                    updateAdminButtons();
                }
            })
            .catch(err => console.error(err));
    }
    
    function renderModules() {
        const wcModule = document.getElementById('module-wordcloud');
        if (wcModule) {
            wcModule.classList.toggle('hidden', !modulesConfig.wordcloud);
            if (modulesConfig.wordcloud) renderCloud();
        }
        
        const pdfModule = document.getElementById('module-pdf');
        if (pdfModule) {
            pdfModule.classList.toggle('hidden', !modulesConfig.pdf_viewer);
            if (modulesConfig.pdf_viewer) {
                // ── KEY FIX ──────────────────────────────────────────────────
                // Only call loadPdf() from the poll path when we haven't loaded
                // a PDF yet, or when the server reports a different file.
                // If the PDF is already loaded and the filename hasn't changed,
                // skip entirely so zoom / scroll state is never touched.
                if (!pdfIsLoaded) {
                    loadPdf();
                } else {
                    // Quick lightweight check: has the server's file changed?
                    checkPdfChanged();
                }
                // ─────────────────────────────────────────────────────────────
            }
        }
        
        const emojiModule = document.getElementById('module-emoji');
        if (emojiModule) {
            emojiModule.classList.toggle('hidden', !modulesConfig.emoji_meter);
            if (modulesConfig.emoji_meter) updateEmojiStats();
        }
        
        const qrModule = document.getElementById('module-qr');
        if (qrModule) {
            if (modulesConfig.qr_link) {
                qrModule.classList.remove('hidden');
                generateQR();
            } else {
                qrModule.classList.add('hidden');
            }
        }
    }

    // Lightweight poll helper: fetch PDF info and reload only if the file changed.
    function checkPdfChanged() {
        fetch(API + '?action=get_pdf_info')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.hasPdf) {
                    // PDF was deleted server-side — clear the viewer.
                    if (pdfIsLoaded) {
                        pdfIsLoaded = false;
                        currentPdfFilename = '';
                        lastKnownServerFilename = null;
                        pdfDoc = null;
                        const viewer = document.getElementById('pdf-viewer');
                        if (viewer) {
                            viewer.innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;">📄</p><p>No lesson material uploaded yet</p></div></div>';
                        }
                        const indicator = document.getElementById('page-indicator');
                        if (indicator) indicator.classList.add('hidden');
                    }
                    return;
                }

                // Same file as before — do nothing, preserving zoom & scroll.
                if (data.filename === lastKnownServerFilename) return;

                // File changed — do a full reload.
                pdfIsLoaded = false;
                loadPdf();
            })
            .catch(err => console.error('checkPdfChanged error:', err));
    }
    
    function updateAdminButtons() {
        const btnWc   = document.getElementById('btn-module-wordcloud');
        const btnPdf  = document.getElementById('btn-module-pdf');
        const btnEmoji= document.getElementById('btn-module-emoji');
        const btnQr   = document.getElementById('btn-module-qr');
        
        if (btnWc) {
            btnWc.classList.toggle('active', modulesConfig.wordcloud);
            btnWc.textContent = (modulesConfig.wordcloud ? '✅ ' : '☁️ ') + 'Word Cloud';
        }
        if (btnPdf) {
            btnPdf.classList.toggle('active', modulesConfig.pdf_viewer);
            btnPdf.textContent = (modulesConfig.pdf_viewer ? '✅ ' : '📄 ') + 'PDF';
        }
        if (btnEmoji) {
            btnEmoji.classList.toggle('active', modulesConfig.emoji_meter);
            btnEmoji.textContent = (modulesConfig.emoji_meter ? '✅ ' : '📱 ') + 'Emoji';
        }
        if (btnQr) {
            btnQr.classList.toggle('active', modulesConfig.qr_link);
            btnQr.textContent = (modulesConfig.qr_link ? '✅ ' : '🔗 ') + 'QR Link';
        }
    }
    
    function toggleAdminPanel() {
        if (!isAdmin) return;
        const statsCard = document.getElementById('emoji-stats-card');
        const btn = document.getElementById('btn-emoji-stats');
        
        if (statsCard && btn) {
            const isHidden = statsCard.classList.contains('hidden');
            
            document.querySelectorAll('#admin-panel .admin-card').forEach(card => {
                card.classList.add('hidden');
            });
            
            if (isHidden) {
                statsCard.classList.remove('hidden');
                document.getElementById('admin-panel').classList.remove('hidden');
                document.getElementById('admin-panel').classList.add('visible');
                btn.classList.add('active');
                btn.textContent = '📊 Stats ON';
                updateAdminPanel();
            } else {
                statsCard.classList.add('hidden');
                document.getElementById('admin-panel').classList.add('hidden');
                document.getElementById('admin-panel').classList.remove('visible');
                btn.classList.remove('active');
                btn.textContent = '📊 Stats';
            }
        }
    }
    
    function updateAdminPanel() {
        if (!isAdmin) return;
        
        fetch(API + '?action=get_emoji_stats')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('admin-lap-number').textContent = data.lapNumber || 1;
                    document.getElementById('admin-total-votes').textContent = data.allTime.total || 0;
                    document.getElementById('admin-stat-done').textContent = data.allTime.done || 0;
                    document.getElementById('admin-stat-unsure').textContent = data.allTime.unsure || 0;
                    document.getElementById('admin-stat-pain').textContent = data.allTime.pain || 0;
                    document.getElementById('admin-stat-happy').textContent = data.allTime.happy || 0;
                    document.getElementById('admin-stat-help').textContent = data.allTime.help || 0;
                }
            });
        
        fetch(API + '?action=get_pdf_info')
            .then(r => r.json())
            .then(data => {
                const pdfName = document.getElementById('admin-pdf-name');
                const pdfTime = document.getElementById('admin-pdf-time');
                
                if (data.success && data.hasPdf) {
                    pdfName.textContent = data.original || 'Lesson.pdf';
                    if (data.uploadTime) {
                        const date = new Date(data.uploadTime * 1000);
                        pdfTime.textContent = 'Uploaded: ' + date.toLocaleString();
                    }
                } else {
                    pdfName.textContent = 'No PDF uploaded';
                    pdfTime.textContent = '';
                }
            });
    }
    
    function toggleModule(module) {
        if (!isAdmin) return;
        
        modulesConfig[module] = !modulesConfig[module];
        
        renderModules();
        updateAdminButtons();
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=update_modules_config&wordcloud=' + modulesConfig.wordcloud + '&pdf_viewer=' + modulesConfig.pdf_viewer + '&emoji_meter=' + modulesConfig.emoji_meter + '&qr_link=' + modulesConfig.qr_link
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                modulesConfig[module] = !modulesConfig[module];
                renderModules();
                updateAdminButtons();
                alert('❌ Failed to update module config');
            }
        })
        .catch(err => {
            modulesConfig[module] = !modulesConfig[module];
            renderModules();
            updateAdminButtons();
            console.error('Config sync error:', err);
        });
    }
    
    function toggleDeleteMode() {
        if (!isAdmin) return;
        deleteMode = !deleteMode;
        const btn = document.getElementById('btn-delete-mode');
        const container = document.getElementById('cloud-container');
        
        if (deleteMode) {
            btn.classList.add('active');
            btn.textContent = '✅ Delete ON';
            container.classList.add('admin-mode');
        } else {
            btn.classList.remove('active');
            btn.textContent = '🗑️ Delete';
            container.classList.remove('admin-mode');
        }
    }
    
    function toggleUsernames() {
        if (!isAdmin) return;
        showUsernamesMode = !showUsernamesMode;
        const btn = document.getElementById('btn-usernames');
        const container = document.getElementById('cloud-container');
        
        if (showUsernamesMode) {
            btn.classList.add('active');
            btn.textContent = '👥 Users ON';
            container.classList.add('show-usernames');
        } else {
            btn.classList.remove('active');
            btn.textContent = '👥 Users';
            container.classList.remove('show-usernames');
        }
        renderCloud();
    }
    
    function toggleEmojiLog() {
        if (!isAdmin) return;
        
        showEmojiLogMode = !showEmojiLogMode;
        const section = document.getElementById('emoji-log-section');
        const btn = document.getElementById('btn-emoji-log');
        
        if (showEmojiLogMode) {
            section.classList.remove('hidden');
            section.classList.add('visible');
            btn.classList.add('active');
            btn.textContent = '📋 Log ON';
            refreshEmojiLog();
        } else {
            section.classList.remove('visible');
            section.classList.add('hidden');
            btn.classList.remove('active');
            btn.textContent = '📋 Emoji Log';
        }
    }
    
    function newLap() {
        if (!isAdmin) return;
        if (confirm('🏁 Start new lap?')) {
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_emoji&type=lap'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ New lap #' + data.lap);
                    updateAdminPanel();
                }
            });
        }
    }
    
    function viewPdf() {
        if (!isAdmin) return;
        
        if (!modulesConfig.pdf_viewer) {
            modulesConfig.pdf_viewer = true;
            fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=update_modules_config&wordcloud=' + modulesConfig.wordcloud + '&pdf_viewer=true&emoji_meter=' + modulesConfig.emoji_meter + '&qr_link=' + modulesConfig.qr_link
            });
            const btnPdf = document.getElementById('btn-module-pdf');
            if (btnPdf) {
                btnPdf.classList.add('active');
                btnPdf.textContent = '✅ PDF';
            }
        }
        
        const pdfModule = document.getElementById('module-pdf');
        pdfModule.classList.remove('hidden');
        // Force a fresh load (admin explicitly requested view)
        pdfIsLoaded = false;
        loadPdf();
        pdfModule.scrollIntoView({ behavior: 'smooth' });
    }
    
    function uploadPdf() {
        if (!isAdmin) return;
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.pdf';
        input.onchange = () => {
            const file = input.files[0];
            if (!file) return;
            
            console.log('Uploading:', file.name, 'Size:', (file.size / 1024 / 1024).toFixed(2) + ' MB');
            
            const formData = new FormData();
            formData.append('pdf', file);
            
            fetch(API + '?action=upload_pdf', { method: 'POST', body: formData })
                .then(async r => {
                    const text = await r.text();
                    console.log('Server raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        return { success: false, error: 'Server returned invalid JSON: ' + text.substring(0, 300) };
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ PDF uploaded successfully!');
                        
                        if (!modulesConfig.pdf_viewer) {
                            modulesConfig.pdf_viewer = true;
                            fetch(API, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                body: 'action=update_modules_config&wordcloud=' + modulesConfig.wordcloud + '&pdf_viewer=true&emoji_meter=' + modulesConfig.emoji_meter + '&qr_link=' + modulesConfig.qr_link
                            });
                            const btnPdf = document.getElementById('btn-module-pdf');
                            if (btnPdf) {
                                btnPdf.classList.add('active');
                                btnPdf.textContent = '✅ PDF';
                            }
                        }
                        
                        // Clear stale position data for old file
                        if (currentPdfFilename) {
                            localStorage.removeItem('pdfScroll_' + currentPdfFilename);
                            localStorage.removeItem('pdfScale_' + currentPdfFilename);
                        }
                        pdfIsLoaded = false;
                        currentPdfFilename = '';
                        lastKnownServerFilename = null;
                        
                        viewPdf();
                        updateAdminPanel();
                    } else {
                        let msg = data.error || data.message || 'Unknown error';
                        if (file.name.match(/[а-яА-ЯёЁ]/)) {
                            msg += '\n\n⚠️ The filename contains Russian letters.\nTry renaming the file to English letters only and upload again.';
                        }
                        alert('❌ Upload failed:\n\n' + msg);
                        console.error('Upload failed details:', data);
                    }
                })
                .catch(err => {
                    console.error('Network error during upload:', err);
                    alert('❌ Network error while uploading. Check console (F12) for details.');
                });
        };
        input.click();
    }
    
    function deletePdf() {
        if (!isAdmin) return;
        if (confirm('🗑️ Delete PDF?')) {
            fetch(API + '?action=delete_pdf', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ PDF deleted');
                        if (currentPdfFilename) {
                            localStorage.removeItem('pdfScroll_' + currentPdfFilename);
                            localStorage.removeItem('pdfScale_' + currentPdfFilename);
                        }
                        pdfIsLoaded = false;
                        currentPdfFilename = '';
                        lastKnownServerFilename = null;
                        pdfDoc = null;
                        const viewer = document.getElementById('pdf-viewer');
                        if (viewer) {
                            viewer.innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;">📄</p><p>No lesson material uploaded yet</p></div></div>';
                        }
                        const indicator = document.getElementById('page-indicator');
                        if (indicator) indicator.classList.add('hidden');
                        updateAdminPanel();
                    }
                });
        }
    }
    
    function loadPdf() {
        fetch(API + '?action=get_pdf_info')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.hasPdf) {
                    const viewer = document.getElementById('pdf-viewer');
                    viewer.innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;">📄</p><p>No lesson material uploaded yet</p></div></div>';
                    document.getElementById('page-indicator').classList.add('hidden');
                    pdfIsLoaded = false;
                    lastKnownServerFilename = null;
                    return;
                }

                // ── KEY FIX ──────────────────────────────────────────────────
                // If the file is already loaded and hasn't changed, bail out
                // immediately — do NOT touch scale, scroll, or the DOM.
                if (pdfIsLoaded && data.filename === currentPdfFilename) {
                    lastKnownServerFilename = data.filename;
                    return;
                }
                // ─────────────────────────────────────────────────────────────
                
                currentPdfFilename = data.filename;
                lastKnownServerFilename = data.filename;
                const viewer = document.getElementById('pdf-viewer');
                
                const savedScroll = localStorage.getItem('pdfScroll_' + currentPdfFilename);
                const savedScale  = localStorage.getItem('pdfScale_'  + currentPdfFilename);
                
                scale = savedScale ? parseFloat(savedScale) : 1.0;
                renderedPages = {};
                
                const loadingTask = pdfjsLib.getDocument('data/' + currentPdfFilename);
                loadingTask.promise.then(pdf => {
                    pdfDoc = pdf;
                    totalPages = pdf.numPages;
                    
                    viewer.innerHTML = `
                        <div class="pdf-controls">
                            <button onclick="zoomOut()">🔍−</button>
                            <span id="zoom-label">Zoom: ${(scale * 100).toFixed(0)}%</span>
                            <button onclick="zoomIn()">🔍+</button>
                        </div>
                        <div class="pdf-pages-container" id="pdf-pages"></div>
                    `;
                    
                    document.getElementById('page-indicator').classList.remove('hidden');
                    document.getElementById('total-pages').textContent = totalPages;
                    
                    renderAllPages().then(() => {
                        pdfIsLoaded = true;
                        if (savedScroll) viewer.scrollTop = parseInt(savedScroll);
                    });
                    
                    viewer.onscroll = handleScroll;
                }).catch(reason => {
                    console.error('PDF loading error:', reason);
                    viewer.innerHTML = `<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;color:#e74c3c;">⚠️</p><p>Failed to load PDF</p><p style="font-size:13px;">${reason.message || reason}</p></div></div>`;
                });
            });
    }
    
    async function renderAllPages() {
        const container = document.getElementById('pdf-pages');
        if (!container || !pdfDoc) return;
        container.innerHTML = '';
        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
            await renderPage(pageNum, container);
        }
    }
    
    function renderPage(pageNum, container) {
        return pdfDoc.getPage(pageNum).then(page => {
            const viewport = page.getViewport({scale: scale});
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            canvas.height = viewport.height;
            canvas.width  = viewport.width;
            canvas.id = 'page-' + pageNum;
            const ctx = canvas.getContext('2d');
            return page.render({canvasContext: ctx, viewport: viewport}).promise.then(() => {
                container.appendChild(canvas);
            });
        });
    }
    
    function handleScroll() {
        if (isScrolling) return;
        isScrolling = true;
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            updatePageIndicator();
            savePdfPosition();
            isScrolling = false;
        }, 150);
    }
    
    function updatePageIndicator() {
        const viewer = document.getElementById('pdf-viewer');
        if (!viewer) return;
        const pages = document.querySelectorAll('.pdf-page-canvas');
        let currentPage = 1;
        pages.forEach((canvas, index) => {
            const rect = canvas.getBoundingClientRect();
            const viewerRect = viewer.getBoundingClientRect();
            if (rect.top <= viewerRect.top + 100 && rect.bottom >= viewerRect.top + 100) {
                currentPage = index + 1;
            }
        });
        document.getElementById('current-page').textContent = currentPage;
    }
    
    function savePdfPosition() {
        const viewer = document.getElementById('pdf-viewer');
        if (viewer && currentPdfFilename) {
            localStorage.setItem('pdfScroll_' + currentPdfFilename, viewer.scrollTop);
            localStorage.setItem('pdfScale_'  + currentPdfFilename, scale);
        }
    }
    
    function zoomIn() {
        scale += 0.25;
        reloadPdfWithScale();
    }
    
    function zoomOut() {
        if (scale <= 0.5) return;
        scale -= 0.25;
        reloadPdfWithScale();
    }
    
    function reloadPdfWithScale() {
        const viewer = document.getElementById('pdf-viewer');
        const scrollRatio = viewer ? viewer.scrollTop / Math.max(viewer.scrollHeight, 1) : 0;
        const zoomLabel = document.getElementById('zoom-label');
        if (zoomLabel) zoomLabel.textContent = 'Zoom: ' + (scale * 100).toFixed(0) + '%';
        
        renderedPages = {};
        const container = document.getElementById('pdf-pages');
        if (container) container.innerHTML = '';
        
        renderAllPages().then(() => {
            if (viewer) viewer.scrollTop = Math.round(scrollRatio * viewer.scrollHeight);
            savePdfPosition();
        });
    }
    
    // DYNAMIC QR GENERATION - Creates unique QR for current module instance
    function generateQR() {
        const container = document.getElementById('qr-code');
        const linkDisplay = document.getElementById('qr-link-display');
        if (!container) return;
        
        const dynamicUrl = getCurrentModuleUrl();
        
        const displayUrl = dynamicUrl.replace(/^https?:\/\//, '');
        linkDisplay.textContent = displayUrl;
        linkDisplay.href = dynamicUrl;
        
        container.innerHTML = '';
        
        try {
            new QRCode(container, {
                text: dynamicUrl,
                width: 300,
                height: 300,
                correctLevel: QRCode.CorrectLevel.H
            });
            console.log('QR generated for:', dynamicUrl);
        } catch(e) {
            console.error('QR generation error:', e);
            container.innerHTML = '<p style="color: #e74c3c;">Error generating QR</p>';
        }
    }
    
    function renderCloud() {
        fetch(API + '?action=get_words')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('cloud-container');
                if (!container) return;
                
                if (!data || data.length === 0) {
                    container.innerHTML = '<p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">No words yet. Be first! 👆</p>';
                    return;
                }
                
                container.innerHTML = '';
                data.slice(0, 80).forEach((item, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'cloud-word-wrapper';
                    wrapper.style.animationDelay = (index * 0.03) + 's';
                    
                    const text  = item.display || item.word;
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
                    
                    const badge = document.createElement('span');
                    badge.className = 'count-badge';
                    badge.textContent = count;
                    
                    const userIndicator = document.createElement('div');
                    userIndicator.className = 'user-indicator';
                    userIndicator.textContent = users.length + ' user' + (users.length > 1 ? 's' : '');
                    
                    const tooltip = document.createElement('div');
                    tooltip.className = 'username-tooltip';
                    let uniqueUsers = [...new Set(users)];
                    tooltip.innerHTML = '<div class="names">' + uniqueUsers.map(u => '<span>' + escapeHtml(u) + '</span>').join('') + '</div>';
                    
                    wrapper.appendChild(deleteBtn);
                    wrapper.appendChild(span);
                    wrapper.appendChild(badge);
                    wrapper.appendChild(userIndicator);
                    wrapper.appendChild(tooltip);
                    container.appendChild(wrapper);
                });
                
                if (deleteMode) container.classList.add('admin-mode');
                if (showUsernamesMode) container.classList.add('show-usernames');
            });
    }
    
    function submitWord() {
        const input = document.getElementById('word-input');
        const word = input ? input.value.trim() : '';
        if (!word) return;
        
        const btn = event.target;
        btn.textContent = 'Sending...';
        btn.disabled = true;
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=add_word&word=' + encodeURIComponent(word) + '&username=' + encodeURIComponent(username)
        })
        .then(() => {
            if (input) input.value = '';
            renderCloud();
        })
        .finally(() => {
            btn.textContent = '🚀 Submit';
            btn.disabled = false;
        });
    }
    
    function deleteWord(word) {
        if (!isAdmin || !confirm('Delete "' + word + '"?')) return;
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_word&word=' + encodeURIComponent(word)
        }).then(() => renderCloud());
    }
    
    function resetWords() {
        if (!isAdmin || !confirm('⚠️ Reset ALL words?')) return;
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=reset&type=words'
        }).then(() => {
            alert('✅ Reset!');
            renderCloud();
        });
    }
    
    function resetEmoji(type) {
        if (!isAdmin || !confirm('Reset emoji data?')) return;
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=reset_emoji&type=' + type
        }).then(() => {
            alert('✅ Reset!');
            updateAdminPanel();
        });
    }
    
    function deleteEmojiLog(type, index = -1) {
        if (!isAdmin) return;
        if (type === 'all' && !confirm('⚠️ Delete ALL?')) return;
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_emoji_log&type=' + type + (index >= 0 ? '&index=' + index : '')
        }).then(() => refreshEmojiLog());
    }
    
    function refreshEmojiLog() {
        if (!showEmojiLogMode) return;
        
        fetch(API + '?action=get_emoji_log')
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('emoji-log-body');
                if (!data.success || !data.log || data.log.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #999; padding: 20px;">No votes yet</td></tr>';
                    return;
                }
                
                tbody.innerHTML = data.log.map((entry, index) => {
                    const date = new Date(entry.time * 1000);
                    return '<tr>' +
                        '<td class="col-emoji">' + (EMOJI_MAP[entry.emoji] || entry.emoji) + '</td>' +
                        '<td class="col-user">' + escapeHtml(entry.username || 'Anonymous') + '</td>' +
                        '<td class="col-time">' + date.toLocaleTimeString() + '</td>' +
                        '<td><span class="col-lap">' + (entry.lap || 1) + '</span></td>' +
                        '<td class="col-action"><button class="delete-row-btn" onclick="deleteEmojiLog(\'single\', ' + index + ')">🗑️</button></td>' +
                        '</tr>';
                }).join('');
            });
    }
    
    function submitEmoji(emoji) {
        const now = Date.now();
        if (now - lastVoteTime < 60000) {
            alert('Wait ' + Math.ceil((60000 - (now - lastVoteTime)) / 1000) + 's');
            return;
        }
        
        const btn = document.getElementById('btn-emoji-' + emoji);
        if (!btn) return;
        
        btn.classList.add('disabled');
        btn.disabled = true;
        lastVoteTime = now;
        
        fetch(API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=emoji_vote&emoji=' + emoji + '&username=' + encodeURIComponent(username)
        }).then(() => updateEmojiStats());
        
        setTimeout(() => {
            btn.classList.remove('disabled');
            btn.disabled = false;
        }, 60000);
    }
    
    function updateEmojiStats() {
        fetch(API + '?action=get_emoji_stats')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const lap = data.currentLap;
                document.getElementById('stat-done').textContent   = lap.done   || 0;
                document.getElementById('stat-unsure').textContent = lap.unsure || 0;
                document.getElementById('stat-pain').textContent   = lap.pain   || 0;
                document.getElementById('stat-happy').textContent  = lap.happy  || 0;
                document.getElementById('stat-help').textContent   = lap.help   || 0;
            });
    }
    
    function updateUserCount() {
        fetch(API + '?action=get_user_count')
            .then(r => r.json())
            .then(data => {
                const display = document.getElementById('user-count-display');
                if (display && data.success) {
                    display.textContent = '👥 ' + data.label;
                }
            });
    }
    
    function checkEmojiAnimation() {
        fetch(API + '?action=get_emoji_animation')
            .then(r => r.json())
            .then(data => {
                if (data.emoji) showEmojiAnimation(data.emoji);
            });
    }
    
    function showEmojiAnimation(emoji) {
        const overlay = document.getElementById('emoji-overlay');
        const symbol = EMOJI_MAP[emoji] || emoji;
        
        for (let i = 0; i < 5; i++) {
            setTimeout(() => {
                const el = document.createElement('div');
                el.className = 'floating-emoji';
                el.textContent = symbol;
                el.style.left = (Math.random() * 80 + 10) + '%';
                overlay.appendChild(el);
                setTimeout(() => el.remove(), 3000);
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
        }).then(() => updateUserCount());
    }
    
    function saveUsername() {
        const input = document.getElementById('username-input');
        if (input && input.value.trim()) {
            username = input.value.trim();
            localStorage.setItem('eduUsername', username);
            document.getElementById('user-badge').textContent = '👤 ' + username;
            document.getElementById('login-modal').classList.add('hidden');
            logUserLogin(username);
        }
    }
    
    function showLogin() {
        document.getElementById('login-modal').classList.remove('hidden');
        document.getElementById('username-input').value = '';
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
