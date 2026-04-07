<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Modules - EduLite</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        body { padding: 0; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .game-container { max-width: 1200px; margin: 0 auto; padding: 15px; }
        .game-header { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .game-title { color: #667eea; font-size: 28px; margin: 0 0 15px 0; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; }
        .user-info { display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; }
        .user-badge, .admin-badge { background: #667eea; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 14px; }
        .admin-badge { background: #e74c3c; }
        .change-name-btn { background: #95a5a6; color: white; border: none; padding: 8px 15px; border-radius: 20px; cursor: pointer; font-size: 13px; min-height: auto; min-width: auto; }
        .login-modal, .info-text-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .login-box, .info-text-box { background: white; padding: 30px; border-radius: 15px; text-align: center; max-width: 500px; width: 90%; animation: slideUp 0.3s ease; }
        .info-text-box { max-width: 600px; text-align: left; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .login-box h2, .info-text-box h2 { color: #667eea; margin-bottom: 20px; }
        .login-box input { width: 100%; max-width: none; margin: 10px 0; }
        .login-box button { width: 100%; margin: 10px 0; }
        .info-text-box textarea, .info-text-box input[type="text"], .info-text-box input[type="number"] { width: 100%; padding: 12px; border: 2px solid #667eea; border-radius: 8px; font-size: 15px; resize: vertical; margin-bottom: 15px; font-family: inherit; box-sizing: border-box; }
        .info-text-box input[type="text"], .info-text-box input[type="number"] { min-height: auto; }
        .info-text-box .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .info-text-box .modal-actions button { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .admin-controls { background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 15px; margin-bottom: 15px; text-align: center; }
        .admin-controls p { margin: 0 0 10px 0; color: #856404; font-weight: 600; }
        .admin-controls button { background: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; margin: 5px; }
        .admin-controls button.active { background: #27ae60; }
        .admin-panel { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; display: none; }
        .admin-panel.visible { display: block; }
        .admin-panel h3 { color: #667eea; margin: 0 0 20px 0; font-size: 20px; display: flex; align-items: center; justify-content: space-between; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .admin-card { background: #f8f9fa; border-radius: 10px; padding: 20px; border: 2px solid #e9ecef; }
        .admin-card h4 { color: #667eea; margin: 0 0 15px 0; font-size: 16px; }
        .admin-card button { width: 100%; margin: 5px 0; padding: 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .lap-info { background: #667eea; color: white; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
        .lap-info .lap-number { font-size: 32px; font-weight: 700; }
        .lap-info .lap-label { font-size: 12px; opacity: 0.9; }
        .pdf-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
        .pdf-info .pdf-name { font-weight: 600; color: #667eea; word-break: break-all; }
        .pdf-info .pdf-time { font-size: 12px; color: #999; margin-top: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 15px; }
        .stat-box { background: white; padding: 10px; border-radius: 8px; text-align: center; border: 2px solid #e9ecef; }
        .stat-box .stat-emoji { font-size: 24px; }
        .stat-box .stat-count { font-size: 20px; font-weight: 700; color: #667eea; }
        .stat-box .stat-label { font-size: 10px; color: #999; text-transform: uppercase; }
        .emoji-log-section { background: rgba(255,255,255,0.98); border-radius: 15px 15px 0 0; padding: 20px; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); position: fixed; bottom: 0; left: 0; right: 0; z-index: 900; max-height: 40vh; overflow-y: auto; display: none; border-top: 3px solid #667eea; }
        .emoji-log-section.visible { display: block; animation: slideUpLog 0.3s ease; }
        @keyframes slideUpLog { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .emoji-log-section h3 { color: #667eea; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; justify-content: space-between; }
        .emoji-log-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .emoji-log-table th { background: #667eea; color: white; padding: 8px; text-align: left; position: sticky; top: 0; }
        .emoji-log-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .emoji-log-table tr:hover { background: #f8f9fa; }
        .emoji-log-table .col-emoji { font-size: 18px; width: 40px; text-align: center; }
        .emoji-log-table .col-user { font-weight: 600; }
        .emoji-log-table .col-time { color: #999; font-size: 10px; }
        .emoji-log-table .col-lap { background: #667eea; color: white; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: 600; display: inline-block; }
        .emoji-log-table .col-action { text-align: center; }
        .delete-row-btn { background: #e74c3c; color: white; border: none; padding: 3px 8px; border-radius: 4px; cursor: pointer; font-size: 10px; }
        .module-section { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; }
        .module-section h2 { color: #667eea; margin: 0 0 20px 0; font-size: 22px; text-align: center; }
        .input-section { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; }
        .input-section h2 { color: #667eea; margin: 0 0 15px 0; font-size: 20px; text-align: center; }
        .input-wrapper { display: flex; gap: 10px; flex-wrap: wrap; }
        .input-wrapper input { flex: 1; min-width: 200px; font-size: 20px; padding: 20px 25px; margin: 0; border: 2px solid #667eea; }
        .input-wrapper button { font-size: 20px; padding: 20px 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); white-space: nowrap; }
        #cloud-container { min-height: 400px; background: #fafafa; border-radius: 10px; padding: 30px; display: flex; flex-wrap: wrap; justify-content: center; align-content: center; gap: 15px; }
        .cloud-word-wrapper { display: inline-block; position: relative; margin: 8px; opacity: 0; animation: fadeIn 0.3s ease forwards; }
        .cloud-word { display: inline-block; padding: 12px 24px; background: rgba(74, 144, 226, 0.1); border-radius: 25px; border: 2px solid rgba(74, 144, 226, 0.3); font-size: 24px; font-weight: 600; color: #2c3e50; cursor: default; transition: all 0.2s ease; }
        .cloud-word:hover { transform: scale(1.05); z-index: 10; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .cloud-sentence { display: inline-block; padding: 15px 25px; background: rgba(102, 126, 234, 0.1); border-radius: 15px; border: 2px solid rgba(102, 126, 234, 0.3); font-size: 18px; font-weight: 500; color: #2c3e50; cursor: default; transition: all 0.2s ease; max-width: 100%; text-align: center; line-height: 1.4; }
        .cloud-sentence:hover { transform: scale(1.02); z-index: 10; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        #sentences-container { min-height: 400px; background: #fafafa; border-radius: 10px; padding: 30px; display: flex; flex-wrap: wrap; justify-content: center; align-content: center; gap: 15px; }
        .delete-btn { position: absolute; top: -6px; left: -6px; width: 16px; height: 16px; background: #e74c3c; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 12px; font-weight: bold; display: none; align-items: center; justify-content: center; z-index: 20; opacity: 0.6; }
        .delete-btn:hover { opacity: 1; }
        .admin-mode .delete-btn { display: flex; }
        .count-badge { position: absolute; top: -6px; right: -6px; background: #e74c3c; color: white; font-size: 11px; font-weight: 700; min-width: 20px; height: 20px; line-height: 20px; text-align: center; border-radius: 10px; }
        .user-indicator { position: absolute; bottom: -8px; right: 50%; transform: translateX(50%); background: #3498db; color: white; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 10px; display: none; white-space: nowrap; }
        .show-usernames .user-indicator { display: block; }
        .username-tooltip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #2c3e50; color: white; padding: 10px 15px; border-radius: 8px; font-size: 12px; max-width: 250px; text-align: center; display: none; z-index: 100; margin-bottom: 10px; }
        .show-usernames .cloud-word-wrapper:hover .username-tooltip { display: block; }
        .username-tooltip .names { margin: 0; line-height: 1.6; max-height: 150px; overflow-y: auto; }
        .username-tooltip .names span { display: inline-block; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; margin: 2px; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        .module-section.info-text-section { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; font-size: 17px; line-height: 1.6; color: #2c3e50; }
        .info-text-content { white-space: pre-wrap; word-break: break-word; }
        .pdf-viewer-container { height: 70vh; background: #525659; border-radius: 10px; overflow-y: auto; position: relative; }
        .pdf-pages-container { padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 20px; }
        .pdf-page-canvas { display: block; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.4); max-width: 100%; width: 100%; cursor: pointer; }
        .no-pdf { display: flex; align-items: center; justify-content: center; height: 100%; color: #999; font-size: 18px; text-align: center; padding: 40px; }
        
        /* INDEPENDENT PAGE INDICATOR PANEL */
        .page-indicator-panel {
            position: fixed; top: 85px; left: 50%; transform: translateX(-50%);
            background: rgba(30, 41, 59, 0.9); backdrop-filter: blur(8px);
            color: #fff; padding: 10px 22px; border-radius: 30px;
            font-size: 14px; font-weight: 600; z-index: 950;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none; display: flex; align-items: center; gap: 8px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .page-indicator-panel.hidden {
            opacity: 0; transform: translateX(-50%) translateY(-10px);
        }

        .pinned-pdf-container { background: #fff; border-radius: 12px; padding: 15px; text-align: center; max-width: 100%; overflow-x: auto; }
        .pinned-pdf-canvas { display: block; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.2); max-width: 100%; height: auto; }
        .emoji-meter-section { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; text-align: center; }
        .emoji-meter-section h2 { color: #667eea; margin: 0 0 20px 0; font-size: 22px; }
        .emoji-buttons { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
        .emoji-btn { background: white; border: 3px solid #ddd; border-radius: 15px; padding: 15px 25px; cursor: pointer; transition: all 0.2s; min-width: 100px; min-height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; }
        .emoji-btn:hover { transform: scale(1.05); border-color: #667eea; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
        .emoji-btn:active { transform: scale(0.95); }
        .emoji-btn.disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(100%); }
        .emoji-btn .emoji-icon { font-size: 40px; }
        .emoji-btn .emoji-label { font-size: 13px; font-weight: 600; color: #666; }
        .emoji-stats { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
        .emoji-stat { background: #f8f9fa; padding: 10px 20px; border-radius: 10px; min-width: 80px; text-align: center; }
        .emoji-stat .stat-emoji { font-size: 24px; margin-bottom: 5px; }
        .emoji-stat .stat-count { font-size: 20px; font-weight: 700; color: #667eea; }
        .emoji-stat .stat-label { font-size: 11px; color: #999; text-transform: uppercase; }
        .user-count-indicator { background: #3498db; color: white; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; margin-left: 10px; }
        .live-indicator { display: inline-flex; align-items: center; gap: 6px; background: #27ae60; color: white; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .live-dot { width: 8px; height: 8px; background: white; border-radius: 50%; animation: pulse 1s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        .emoji-animation-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 9999; display: none; }
        .emoji-animation-overlay.active { display: block; }
        .floating-emoji { position: absolute; font-size: 80px; animation: floatUp 3s ease-out forwards; opacity: 0; }
        @keyframes floatUp { 0% { transform: translateY(100vh) scale(0.5); opacity: 1; } 50% { opacity: 1; } 100% { transform: translateY(-100px) scale(1.5); opacity: 0; } }
        .hidden { display: none !important; }
        .qr-section { background: rgba(255,255,255,0.95); border-radius: 15px; padding: 20px 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin-bottom: 15px; text-align: center; width: 100%; box-sizing: border-box; }
        #qr-container { display: flex; justify-content: center; margin: 15px 0; }
        #qr-code { width: 100%; max-width: 300px; height: auto; aspect-ratio: 1/1; display: flex; justify-content: center; align-items: center; }
        #qr-code img, #qr-code canvas { max-width: 100%; height: auto !important; }
        .qr-link { display: block; margin-top: 12px; color: #667eea; font-weight: 600; font-size: 13px; word-break: break-all; padding: 0 10px; }
        #export-modal .login-box { max-width: 480px; text-align: left; }
        #export-modal label { cursor: pointer; display: flex; align-items: center; gap: 8px; margin: 8px 0; }
        #export-modal input[type="checkbox"] { cursor: pointer; width: 16px; height: 16px; }
        #snapshot-list { max-height: 250px; overflow-y: auto; margin-bottom: 15px; border: 1px solid #eee; border-radius: 8px; }
        .snap-item { padding: 10px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px; background: #fff; }
        .snap-item:last-child { border-bottom: none; }
        .snap-meta { flex: 1; font-size: 13px; }
        .snap-meta strong { display: block; }
        .snap-meta small { color: #666; font-size: 11px; display: block; margin-top: 2px; }
        .modal-actions-flex { display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; margin-top: 10px; }
        @media (max-width: 480px) {
            .game-title { font-size: 22px; }
            .input-wrapper { flex-direction: column; }
            .input-wrapper input, .input-wrapper button { width: 100%; }
            .emoji-btn { min-width: 80px; min-height: 70px; padding: 12px 20px; }
            .emoji-btn .emoji-icon { font-size: 32px; }
            .emoji-stats { gap: 10px; }
            .emoji-stat { min-width: 60px; padding: 8px 12px; }
            .admin-grid { grid-template-columns: 1fr; }
            #qr-code { max-width: 220px; }
            .qr-link { font-size: 12px; }
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

<!-- Info Text Edit Modal -->
<div id="info-text-modal" class="info-text-modal hidden">
    <div class="info-text-box">
        <h2>✏️ Edit Info Text</h2>
        <p style="color:#666;margin-bottom:15px;font-size:14px;">Edit the text that appears at the top when the Info module is enabled:</p>
        <textarea id="modal-info-text" rows="10" placeholder="Enter your info text here..."></textarea>
        <div class="modal-actions">
            <button type="button" onclick="closeInfoTextEditor()" style="background:#95a5a6;color:white;">Cancel</button>
            <button type="button" onclick="saveInfoTextFromModal()" class="btn-success">💾 Save Changes</button>
        </div>
    </div>
</div>

<!-- Class Name Edit Modal -->
<div id="class-name-modal" class="info-text-modal hidden">
    <div class="info-text-box">
        <h2>🏫 Set Class Name</h2>
        <p style="color:#666;margin-bottom:15px;font-size:14px;">This will appear in the header and be included in exports:</p>
        <input type="text" id="modal-class-name" placeholder="e.g., Math 101 - Spring 2026">
        <div class="modal-actions">
            <button type="button" onclick="closeClassNameModal()" style="background:#95a5a6;color:white;">Cancel</button>
            <button type="button" onclick="saveClassNameFromModal()" class="btn-success">💾 Save</button>
        </div>
    </div>
</div>

<!-- PDF Pin Modal -->
<div id="pdf-pin-modal" class="info-text-modal hidden">
    <div class="info-text-box" style="max-width:440px; text-align: center;">
        <h2>📌 Pin a PDF Page</h2>
        <p style="color:#666; margin-bottom:15px; font-size:14px;">Click a page in the viewer below to select it, then pin it.</p>
        
        <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <div style="text-align:left;">
                <strong>📖 Selected Page:</strong><br>
                <span id="pin-current-page-display" style="font-weight:700; font-size:18px;">? (Click a page)</span>
            </div>
            <button type="button" onclick="pinCurrentViewedPage()" class="btn-primary" style="padding:8px 14px; font-size:12px;">📌 Pin Selected</button>
        </div>

        <div style="margin-bottom:15px;">
            <label style="font-size:13px; color:#666; display:block; margin-bottom:5px;">Or enter a specific page number:</label>
            <input type="number" id="modal-pdf-pin-input" placeholder="e.g., 12" min="1" style="width:100%; padding:10px; border:2px solid #667eea; border-radius:8px; font-size:15px; text-align:center; box-sizing:border-box;">
        </div>

        <p id="modal-pdf-pin-status" style="font-size:12px; color:#999; margin:5px 0 15px 0;"></p>
        
        <div class="modal-actions" style="justify-content: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="closePdfPinModal()" style="background:#95a5a6;color:white;">Cancel</button>
            <button type="button" onclick="clearPdfPinFromModal()" class="btn-danger">Clear Pin</button>
            <button type="button" onclick="pinSpecificPage()" class="btn-success">📌 Pin Entered #</button>
        </div>
    </div>
</div>

<!-- Export & Delete Snapshots Modal -->
<div id="export-modal" class="login-modal hidden">
    <div class="login-box" style="max-width:480px;">
        <h2>📤 Manage Snapshots</h2>
        <p style="color:#666;margin-bottom:15px;font-size:14px;">Select snapshots to export or delete:</p>
        <div id="snapshot-list"><p style="color:#999;text-align:center; padding: 20px;">Loading snapshots...</p></div>
        <div style="margin:10px 0; padding:8px; background:#f8f9fa; border-radius:6px;">
            <label style="margin:0; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="select-all-check" onchange="toggleSelectAll(this)"><strong>Select All / Deselect All</strong>
            </label>
        </div>
        <div class="modal-actions-flex">
            <button type="button" onclick="closeExportModal()" style="background:#95a5a6;color:white;">Cancel</button>
            <button type="button" onclick="performDeleteSelected()" class="btn-danger">🗑️ Delete Selected</button>
            <button type="button" onclick="performExportSelected()" class="btn-primary">📤 Export Selected</button>
            <button type="button" onclick="performExportAll()" class="btn-success">📥 Export ALL</button>
        </div>
    </div>
</div>

<!-- Emoji Animation Overlay -->
<div class="emoji-animation-overlay" id="emoji-overlay"></div>

<div class="game-container">
    <!-- Header -->
    <div class="game-header">
        <h1 class="game-title">
            <span>🎯</span><span>EduLite Modules</span>
            <span class="live-indicator"><span class="live-dot"></span>LIVE</span>
            <span class="user-count-indicator" id="user-count-display">👥 0 users</span>
        </h1>
        <div class="user-info">
            <span class="user-badge" id="user-badge">👤 Guest</span>
            <span class="admin-badge hidden" id="admin-badge">🔑 Admin</span>
            <button type="button" class="change-name-btn" onclick="showLogin()">Change Name</button>
            <button type="button" class="change-name-btn" onclick="goHome()">← Back</button>
        </div>
    </div>

    <!-- INDEPENDENT PAGE INDICATOR PANEL -->
    <div id="pdf-page-indicator" class="page-indicator-panel">
        📖 Page <span id="current-page-num">?</span> / <span id="total-pages-num">-</span>
    </div>
   
    <!-- Admin Controls -->
    <div class="admin-controls hidden" id="admin-controls">
        <p>⚠️ Admin Mode: Quick Controls</p>
        <button type="button" onclick="toggleDeleteMode()" id="btn-delete-mode">🗑️ Delete</button>
        <button type="button" onclick="toggleUsernames()" id="btn-usernames">👥 Users</button>
        <button type="button" onclick="toggleAdminPanel()" id="btn-emoji-stats">📊 Stats</button>
        <button type="button" onclick="newLap()" id="btn-new-lap">🏁 New Lap</button>
        <button type="button" onclick="resetEmoji('all')" id="btn-reset-emoji">🔄 Reset Emoji</button>
        <button type="button" onclick="resetCloud()" id="btn-reset-cloud">🔄 Reset Cloud</button>
        <button type="button" onclick="toggleEmojiLog()" id="btn-emoji-log">📋 Emoji Log</button>
        <button type="button" onclick="toggleModule('wordcloud')" id="btn-module-wordcloud">☁️ Word Cloud</button>
        <button type="button" onclick="toggleModule('sentences_cloud')" id="btn-module-sentences">💬 Sentences</button>
        <button type="button" onclick="toggleModule('pdf_viewer')" id="btn-module-pdf">📄 PDF</button>
        <button type="button" onclick="toggleModule('info_text')" id="btn-module-info">ℹ️ Info Text</button>
        <button type="button" onclick="openInfoTextEditor()" id="btn-edit-info" style="background:#9b59b6;">✏️ Edit Info</button>
        <button type="button" onclick="openClassNameModal()" id="btn-class-name" style="background:#8e44ad;">🏫 Class Name</button>
        <button type="button" onclick="createSnapshot()" id="btn-snapshot" style="background:#2ecc71;">📸 Snapshot</button>
        <button type="button" onclick="uploadPdf()" id="btn-quick-upload-pdf" style="background: #27ae60;">📤 Upload PDF</button>
        <button type="button" onclick="viewPdf()" style="background: #3498db;">👁️ View PDF</button>
        <button type="button" onclick="openPdfPinModal()" id="btn-pdf-pin" style="background:#9b59b6;">📌 Pin Page</button>
        <button type="button" onclick="toggleModule('emoji_meter')" id="btn-module-emoji">📱 Emoji</button>
        <button type="button" onclick="toggleModule('qr_link')" id="btn-module-qr">🔗 QR Link</button>
        <button type="button" onclick="openExportModal()" id="btn-export" style="background:#9b59b6;">📤 Export</button>
    </div>
   
    <!-- Admin Panel -->
    <div class="admin-panel hidden" id="admin-panel">
        <h3><span>⚙️ Admin Dashboard</span><button type="button" onclick="toggleAdminPanel()" style="background: #95a5a6; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px;">✕ Close</button></h3>
        <div class="admin-grid">
            <div class="admin-card">
                <h4>🏁 Lap Management</h4>
                <div class="lap-info"><div class="lap-number" id="admin-lap-number">1</div><div class="lap-label">Current Lap</div></div>
                <button class="btn-primary" onclick="newLap()">🏁 Start New Lap</button>
            </div>
            <div class="admin-card">
                <h4>📄 PDF Management</h4>
                <div class="pdf-info" id="admin-pdf-info"><div class="pdf-name" id="admin-pdf-name">No PDF uploaded</div><div class="pdf-time" id="admin-pdf-time"></div></div>
                <button class="btn-success" onclick="uploadPdf()">📤 Upload/Change PDF</button>
                <button type="button" class="btn-primary" onclick="viewPdf()">👁️ View Current PDF</button>
                <button class="btn-danger" onclick="deletePdf()">🗑️ Delete PDF</button>
            </div>
            <div class="admin-card">
                <h4>🔄 Reset Controls</h4>
                <button class="btn-danger" onclick="resetCloud()">🗑️ Clear Cloud Data</button>
                <button class="btn-warning" onclick="resetEmoji('lap')">🔄 Reset Current Lap</button>
                <button class="btn-danger" onclick="resetEmoji('all')">⚠️ Reset All Emoji</button>
            </div>
            <div class="admin-card" id="emoji-stats-card">
                <h4>📊 Emoji Stats (All-Time)</h4>
                <div class="stats-grid" id="admin-emoji-stats">
                    <div class="stat-box"><div class="stat-emoji">✅</div><div class="stat-count" id="admin-stat-done">0</div><div class="stat-label">Done</div></div>
                    <div class="stat-box"><div class="stat-emoji">🤔</div><div class="stat-count" id="admin-stat-unsure">0</div><div class="stat-label">Unsure</div></div>
                    <div class="stat-box"><div class="stat-emoji">😰</div><div class="stat-count" id="admin-stat-pain">0</div><div class="stat-label">Pain</div></div>
                    <div class="stat-box"><div class="stat-emoji">😊</div><div class="stat-count" id="admin-stat-happy">0</div><div class="stat-label">Happy</div></div>
                    <div class="stat-box"><div class="stat-emoji">🙋</div><div class="stat-count" id="admin-stat-help">0</div><div class="stat-label">Help</div></div>
                </div>
                <p style="font-size: 11px; color: #999; margin-top: 10px;">Total: <span id="admin-total-votes">0</span></p>
            </div>
        </div>
    </div>
   
    <!-- INFO TEXT MODULE -->
    <div class="module-section hidden info-text-section" id="module-info-text">
        <h2>ℹ️ Information</h2>
        <div class="info-text-content" id="info-text-content"></div>
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
        <div id="cloud-container"><p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">Loading...</p></div>
    </div>
   
    <!-- Sentences Cloud Module -->
    <div class="module-section hidden" id="module-sentences-cloud">
        <h2>💬 Sentences Cloud</h2>
        <div class="input-section">
            <h2>✍️ Add Your Sentence</h2>
            <div class="input-wrapper">
                <textarea id="sentence-input" placeholder="Type a sentence or phrase..." rows="3" style="flex:1;min-width:200px;font-size:16px;padding:15px 20px;margin:0;border:2px solid #667eea;border-radius:12px;resize:vertical;"></textarea>
                <button type="button" onclick="submitSentence()">🚀 Submit</button>
            </div>
        </div>
        <div id="sentences-container"><p style="color: #999; text-align: center; padding: 40px; font-size: 22px;">Loading...</p></div>
    </div>
   
    <!-- PDF Viewer Module -->
    <div class="module-section hidden" id="module-pdf">
        <h2>📄 PDF Viewer</h2>
        <div class="pdf-viewer-container" id="pdf-viewer">
            <div class="no-pdf"><div><p style="font-size: 48px; margin-bottom: 20px;">📄</p><p>No lesson material uploaded yet</p></div></div>
        </div>
    </div>

    <!-- PINNED PDF MODULE -->
    <div class="module-section hidden" id="module-pdf-pinned">
        <h2>📌 Pinned Page</h2>
        <div class="pinned-pdf-container" id="pinned-pdf-wrapper"></div>
    </div>
   
    <!-- Emoji Meter Module -->
    <div class="module-section hidden" id="module-emoji">
        <h2>📱 How Are You Doing?</h2>
        <p style="color: #666; margin-bottom: 20px;">Tap once every 60 seconds</p>
        <div class="emoji-buttons">
            <button type="button" class="emoji-btn" onclick="submitEmoji('done')" id="btn-emoji-done"><span class="emoji-icon">✅</span><span class="emoji-label">Done</span></button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('unsure')" id="btn-emoji-unsure"><span class="emoji-icon">🤔</span><span class="emoji-label">Unsure</span></button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('pain')" id="btn-emoji-pain"><span class="emoji-icon">😰</span><span class="emoji-label">Pain</span></button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('happy')" id="btn-emoji-happy"><span class="emoji-icon">😊</span><span class="emoji-label">Happy</span></button>
            <button type="button" class="emoji-btn" onclick="submitEmoji('help')" id="btn-emoji-help"><span class="emoji-icon">🙋</span><span class="emoji-label">Help</span></button>
        </div>
        <div class="emoji-stats" id="public-emoji-stats">
            <div class="emoji-stat"><div class="stat-emoji">✅</div><div class="stat-count" id="stat-done">0</div><div class="stat-label">Done</div></div>
            <div class="emoji-stat"><div class="stat-emoji">🤔</div><div class="stat-count" id="stat-unsure">0</div><div class="stat-label">Unsure</div></div>
            <div class="emoji-stat"><div class="stat-emoji">😰</div><div class="stat-count" id="stat-pain">0</div><div class="stat-label">Pain</div></div>
            <div class="emoji-stat"><div class="stat-emoji">😊</div><div class="stat-count" id="stat-happy">0</div><div class="stat-label">Happy</div></div>
            <div class="emoji-stat"><div class="stat-emoji">🙋</div><div class="stat-count" id="stat-help">0</div><div class="stat-label">Help</div></div>
        </div>
    </div>
   
    <!-- QR Link Module -->
    <div class="module-section hidden" id="module-qr">
        <h2>🔗 Join Link</h2>
        <div id="qr-container"><div id="qr-code"></div></div>
        <a href="#" class="qr-link" id="qr-link-display" target="_blank">Loading...</a>
    </div>
</div>

<!-- Emoji Log Section -->
<div class="emoji-log-section" id="emoji-log-section">
    <h3><span>📋 Emoji Vote Log</span><div><button type="button" onclick="deleteEmojiLog('all')" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; margin-right: 5px;">🗑️ Clear All</button><button type="button" onclick="refreshEmojiLog()" style="background: #667eea; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">🔄 Refresh</button><button type="button" onclick="toggleEmojiLog()" style="background: #95a5a6; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; margin-left: 5px;">✕ Close</button></div></h3>
    <table class="emoji-log-table" id="emoji-log-table">
        <thead><tr><th>Emoji</th><th>User</th><th>Time</th><th>Lap</th><th>Action</th></tr></thead>
        <tbody id="emoji-log-body"><tr><td colspan="5" style="text-align: center; color: #999; padding: 20px;">Loading...</td></tr></tbody>
    </table>
</div>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    const API = 'api.php';
    let username = localStorage.getItem('eduUsername') || '';
    let isAdmin = false;
    let modulesConfig = {};
    let lastVoteTime = 0;
    let pollInterval = null;
    let deleteMode = false;
    let showUsernamesMode = false;
    let showEmojiLogMode = false;
    let currentPdfFilename = '';
    let pdfDoc = null;
    let currentInfoText = '';
    let currentViewedPage = 0; // Starts at 0, waits for user input
    let isPdfRendering = false;
    const COLOR_PALETTE = ['#2c3e50', '#34495e', '#5d4e6d', '#4a5568', '#2d5d7c', '#6b4c7a', '#3d6b5f', '#7c524a', '#4a6b7c', '#5a4d7a'];
    const EMOJI_MAP = {'done': '✅', 'unsure': '🤔', 'pain': '😰', 'happy': '😊', 'help': '🙋'};
   
    document.addEventListener('DOMContentLoaded', () => {
        if (username) { document.getElementById('user-badge').textContent = '👤 ' + username; logUserLogin(username); }
        else { document.getElementById('login-modal').classList.remove('hidden'); }
        checkAdminStatus();
        loadModulesConfig();
        pollInterval = setInterval(loadModulesConfig, 5000);
        setInterval(updateEmojiStats, 3000);
        setInterval(updateUserCount, 5000);
        setInterval(checkEmojiAnimation, 1000);
        setInterval(checkPdfPin, 4000);
        setTimeout(() => { const input = document.getElementById('word-input'); if (input) input.focus(); }, 500);
    });
   
    function goHome() { window.location.replace('index.php'); }
    function checkAdminStatus() {
        fetch(API + '?action=check_session').then(r => r.json()).then(data => {
            isAdmin = data.is_admin || false;
            if (isAdmin) { document.getElementById('admin-badge').classList.remove('hidden'); document.getElementById('admin-controls').classList.remove('hidden'); loadClassName(); }
        }).catch(err => console.error(err));
    }
    function loadModulesConfig() {
        fetch(API + '?action=get_modules_config').then(r => r.json()).then(data => {
            if (data.success) {
                const s = data.config;
                modulesConfig = { wordcloud: s.wordcloud !== undefined ? s.wordcloud : false, sentences_cloud: s.sentences_cloud !== undefined ? s.sentences_cloud : false, pdf_viewer: s.pdf_viewer !== undefined ? s.pdf_viewer : false, emoji_meter: s.emoji_meter !== undefined ? s.emoji_meter : true, qr_link: s.qr_link !== undefined ? s.qr_link : false, info_text: s.info_text !== undefined ? s.info_text : false };
                loadInfoText(); renderModules(); updateAdminButtons();
            }
        }).catch(err => console.error(err));
    }
    function loadInfoText() {
        fetch(API + '?action=get_info_text').then(r => r.json()).then(data => {
            if (data.success) { currentInfoText = data.text || ''; const c = document.getElementById('info-text-content'); if (c) c.innerHTML = currentInfoText.replace(/\n/g, '<br>'); }
        });
    }
    function saveInfoText() {
        if (!isAdmin) return; const t = document.getElementById('admin-info-text')?.value.trim() || document.getElementById('modal-info-text')?.value.trim() || '';
        fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=save_info_text&text=' + encodeURIComponent(t) }).then(r => r.json()).then(data => {
            if (data.success) { alert('✅ Info text saved successfully!'); currentInfoText = t; const c = document.getElementById('info-text-content'); if (c) c.innerHTML = t.replace(/\n/g, '<br>'); closeInfoTextEditor(); } else alert('❌ Failed to save info text');
        }).catch(() => alert('❌ Network error while saving'));
    }
    function openInfoTextEditor() { if (!isAdmin) return; const m = document.getElementById('info-text-modal'), t = document.getElementById('modal-info-text'); if (m && t) { t.value = currentInfoText; m.classList.remove('hidden'); } }
    function closeInfoTextEditor() { const m = document.getElementById('info-text-modal'); if (m) m.classList.add('hidden'); }
    function saveInfoTextFromModal() { if (!isAdmin) return; const t = document.getElementById('modal-info-text').value.trim(); fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=save_info_text&text=' + encodeURIComponent(t) }).then(r => r.json()).then(data => { if (data.success) { alert('✅ Info text saved successfully!'); currentInfoText = t; const c = document.getElementById('info-text-content'); if (c) c.innerHTML = t.replace(/\n/g, '<br>'); closeInfoTextEditor(); } else alert('❌ Failed to save info text'); }).catch(() => alert('❌ Network error while saving')); }

    // ========================================================================
    // CLASS NAME MODAL FUNCTIONS
    // ========================================================================
    function loadClassName() {
        fetch(API + '?action=get_class_config').then(r => r.json()).then(data => {
            if (data.success) {
                const name = data.class_name || ''; document.getElementById('modal-class-name').value = name;
                const header = document.querySelector('.game-title');
                if (header) { const e = header.querySelector('.class-badge'); if (e) e.remove(); if (name) { const b = document.createElement('span'); b.className = 'class-badge'; b.textContent = '🏫 ' + name; b.style.cssText = 'background:#8e44ad;color:white;padding:5px 12px;border-radius:15px;font-size:13px;font-weight:600;'; header.appendChild(b); } }
            }
        });
    }
    function openClassNameModal() { if (!isAdmin) return; const m = document.getElementById('class-name-modal'); if (m) m.classList.remove('hidden'); }
    function closeClassNameModal() { const m = document.getElementById('class-name-modal'); if (m) m.classList.add('hidden'); }
    function saveClassNameFromModal() { if (!isAdmin) return; const n = document.getElementById('modal-class-name').value.trim(); fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=save_class_config&class_name=' + encodeURIComponent(n) }).then(r => r.json()).then(d => { if (d.success) { alert('✅ Class name saved!'); loadClassName(); closeClassNameModal(); } else alert('❌ Failed to save class name'); }); }

    // ========================================================================
    // PDF PINNING & RENDERING (CLICK TO SELECT)
    // ========================================================================
    
    async function loadPdf() {
        if (isPdfRendering) return;
        isPdfRendering = true;
        const viewer = document.getElementById('pdf-viewer');
        const indicator = document.getElementById('pdf-page-indicator');
        
        // Reset selection state
        currentViewedPage = 0;
        document.getElementById('current-page-num').textContent = '?';
        document.getElementById('pin-current-page-display').textContent = '? (Click a page)';

        try {
            const infoRes = await fetch(API + '?action=get_pdf_info&t=' + Date.now());
            const infoData = await infoRes.json();
            
            if (!infoData.success || !infoData.hasPdf) {
                viewer.innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;">📄</p><p>No lesson material uploaded yet</p></div></div>';
                pdfDoc = null; currentPdfFilename = '';
                if (indicator) indicator.classList.add('hidden');
                return;
            }

            if (currentPdfFilename === infoData.filename && pdfDoc) {
                checkPdfPin();
                return;
            }

            currentPdfFilename = infoData.filename;
            viewer.innerHTML = '<div class="pdf-pages-container" id="pdf-pages"></div>';
            const container = document.getElementById('pdf-pages');

            pdfDoc = await pdfjsLib.getDocument('data/' + currentPdfFilename + '?t=' + Date.now()).promise;
            container.innerHTML = '';

            // Set Total Pages
            const totalPagesSpan = document.getElementById('total-pages-num');
            if(totalPagesSpan) totalPagesSpan.textContent = pdfDoc.numPages;

            // STRICT SEQUENTIAL RENDERING
            for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                const page = await pdfDoc.getPage(pageNum);
                const scale = 1.5;
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement('canvas');
                canvas.className = 'pdf-page-canvas';
                canvas.dataset.page = pageNum;
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Add click listener to update page number
                canvas.addEventListener('click', () => {
                    currentViewedPage = pageNum;
                    document.getElementById('current-page-num').textContent = pageNum;
                    document.getElementById('pin-current-page-display').textContent = pageNum;
                    // Panel is visible, but ensures user sees the update
                    document.getElementById('pdf-page-indicator').classList.remove('hidden');
                });

                const ctx = canvas.getContext('2d');
                await page.render({ canvasContext: ctx, viewport }).promise;
                container.appendChild(canvas);
            }

            checkPdfPin();
        } catch (err) {
            console.error('PDF render error:', err);
            viewer.innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;color:#e74c3c;">⚠️</p><p>Failed to load PDF. Check file integrity.</p></div></div>';
        } finally {
            isPdfRendering = false;
        }
    }

    async function renderPinnedPage(pageNum) {
        if (!pdfDoc || pageNum < 1 || pageNum > pdfDoc.numPages) return;
        const wrapper = document.getElementById('pinned-pdf-wrapper');
        let canvas = wrapper.querySelector('.pinned-pdf-canvas');
        if (!canvas) { canvas = document.createElement('canvas'); canvas.className = 'pinned-pdf-canvas'; wrapper.appendChild(canvas); }
        try {
            const page = await pdfDoc.getPage(pageNum);
            const scale = 1.5; const viewport = page.getViewport({ scale });
            canvas.height = viewport.height; canvas.width = viewport.width;
            await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
        } catch (err) { console.error('Pinned page render error:', err); }
    }

    function checkPdfPin() {
        if (!pdfDoc) return;
        fetch(API + '?action=get_pdf_config&t=' + Date.now()).then(r => r.json()).then(data => {
            if (data.success) {
                const pp = data.pinned_page; const m = document.getElementById('module-pdf-pinned');
                if (pp > 0 && pp <= pdfDoc.numPages) { if (m) { m.classList.remove('hidden'); renderPinnedPage(pp); } }
                else if (m) m.classList.add('hidden');
            }
        }).catch(err => console.error('Check PDF pin failed', err));
    }

    function showPinStatus(msg, type) {
        const s = document.getElementById('modal-pdf-pin-status');
        s.textContent = msg;
        s.style.color = type === 'success' ? '#27ae60' : type === 'error' ? '#e74c3c' : '#999';
    }

    function openPdfPinModal() {
        if (!isAdmin) return;
        // Sync display with current clicked page
        if(currentViewedPage > 0) {
             document.getElementById('pin-current-page-display').textContent = currentViewedPage;
        } else {
             document.getElementById('pin-current-page-display').textContent = '? (Click a page)';
        }
        
        document.getElementById('modal-pdf-pin-input').value = '';
        showPinStatus('Ready to pin.', 'info');
        fetch(API + '?action=get_pdf_config').then(r => r.json()).then(d => {
            if (d.success && d.pinned_page > 0) showPinStatus(`Currently pinned: Page ${d.pinned_page}`, 'info');
        });
        document.getElementById('pdf-pin-modal').classList.remove('hidden');
    }
    function closePdfPinModal() { document.getElementById('pdf-pin-modal').classList.add('hidden'); }
    
    function pinCurrentViewedPage() {
        // Check if user has clicked a page
        if (currentViewedPage < 1) {
            return showPinStatus('⚠️ Please click a page in the viewer to select it first.', 'error');
        }
        setPdfPin(currentViewedPage);
    }

    function pinSpecificPage() {
        const input = document.getElementById('modal-pdf-pin-input');
        const page = parseInt(input.value);
        if (!pdfDoc) return showPinStatus('⚠️ PDF not loaded yet.', 'error');
        if (isNaN(page) || page < 1 || page > pdfDoc.numPages) return showPinStatus(`⚠️ Enter a valid page (1-${pdfDoc.numPages})`, 'error');
        setPdfPin(page);
    }

    function setPdfPin(page) {
        showPinStatus(`Pinning Page ${page}...`, 'info');
        fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=set_pdf_config&page=${page}` })
        .then(r => r.json()).then(d => {
            if (d.success) {
                showPinStatus(`✅ Pinned Page ${page}!`, 'success');
                setTimeout(() => { closePdfPinModal(); checkPdfPin(); }, 600);
            } else { showPinStatus('❌ Failed to pin page.', 'error'); }
        }).catch(() => showPinStatus('❌ Network error.', 'error'));
    }

    function clearPdfPinFromModal() {
        if (!isAdmin) return;
        showPinStatus('Clearing pin...', 'info');
        fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=set_pdf_config&page=0' })
        .then(r => r.json()).then(d => {
            if (d.success) {
                showPinStatus('✅ Pin cleared.', 'success');
                setTimeout(() => { closePdfPinModal(); const m = document.getElementById('module-pdf-pinned'); if (m) m.classList.add('hidden'); }, 500);
            }
        });
    }

    // ========================================================================
    // CORE UI & MODULE LOGIC
    // ========================================================================
    function renderModules() {
        ['module-info-text', 'module-wordcloud', 'module-sentences-cloud', 'module-pdf', 'module-emoji', 'module-qr'].forEach(id => {
            const el = document.getElementById(id); if (!el) return;
            if (id === 'module-info-text') el.classList.toggle('hidden', !modulesConfig.info_text);
            if (id === 'module-wordcloud') { el.classList.toggle('hidden', !modulesConfig.wordcloud); if (modulesConfig.wordcloud) renderCloud(); }
            if (id === 'module-sentences-cloud') { el.classList.toggle('hidden', !modulesConfig.sentences_cloud); if (modulesConfig.sentences_cloud) renderSentencesCloud(); }
            if (id === 'module-pdf') { el.classList.toggle('hidden', !modulesConfig.pdf_viewer); if (modulesConfig.pdf_viewer) loadPdf(); }
            if (id === 'module-emoji') { el.classList.toggle('hidden', !modulesConfig.emoji_meter); if (modulesConfig.emoji_meter) updateEmojiStats(); }
            if (id === 'module-qr') { el.classList.toggle('hidden', !modulesConfig.qr_link); if (modulesConfig.qr_link) generateQR(); }
        });
    }
    function updateAdminButtons() {
        [['btn-module-wordcloud', modulesConfig.wordcloud], ['btn-module-sentences', modulesConfig.sentences_cloud], ['btn-module-pdf', modulesConfig.pdf_viewer], ['btn-module-emoji', modulesConfig.emoji_meter], ['btn-module-qr', modulesConfig.qr_link], ['btn-module-info', modulesConfig.info_text]].forEach(([id, state]) => { const b = document.getElementById(id); if (b) b.classList.toggle('active', state); });
    }
    function toggleModule(module) {
        if (!isAdmin) return;
        if (module === 'sentences_cloud' && !modulesConfig.sentences_cloud) modulesConfig.wordcloud = false;
        if (module === 'wordcloud' && !modulesConfig.wordcloud) modulesConfig.sentences_cloud = false;
        modulesConfig[module] = !modulesConfig[module];
        renderModules(); updateAdminButtons();
        fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=update_modules_config&wordcloud=${modulesConfig.wordcloud}&sentences_cloud=${modulesConfig.sentences_cloud}&pdf_viewer=${modulesConfig.pdf_viewer}&emoji_meter=${modulesConfig.emoji_meter}&qr_link=${modulesConfig.qr_link}&info_text=${modulesConfig.info_text}` }).then(r => r.json()).then(d => { if (!d.success) { modulesConfig[module] = !modulesConfig[module]; renderModules(); updateAdminButtons(); alert('❌ Failed to update module config'); } }).catch(e => { modulesConfig[module] = !modulesConfig[module]; renderModules(); updateAdminButtons(); console.error(e); });
    }
    function waitForPdfReady(maxAttempts = 10, interval = 300) { let a = 0; return new Promise((res, rej) => { const c = () => fetch(API + '?action=get_pdf_info&t=' + Date.now()).then(r => r.json()).then(d => d.success && d.hasPdf ? res(d) : a < maxAttempts ? (a++, setTimeout(c, interval)) : rej()).catch(e => a < maxAttempts ? (a++, setTimeout(c, interval)) : rej()); c(); }); }
    function toggleAdminPanel() { if (!isAdmin) return; const s = document.getElementById('emoji-stats-card'), b = document.getElementById('btn-emoji-stats'); if (!s || !b) return; const h = s.classList.contains('hidden'); document.querySelectorAll('#admin-panel .admin-card').forEach(c => c.classList.add('hidden')); if (h) { s.classList.remove('hidden'); document.getElementById('admin-panel').classList.add('visible'); b.classList.add('active'); b.textContent = '📊 Stats ON'; updateAdminPanel(); } else { s.classList.add('hidden'); document.getElementById('admin-panel').classList.remove('visible'); b.classList.remove('active'); b.textContent = '📊 Stats'; } }
    function updateEmojiStats() { fetch(API + '?action=get_emoji_stats&t=' + Date.now()).then(r => r.json()).then(d => { if (!d.success) return; const l = d.currentLap || {}; document.getElementById('stat-done').textContent = l.done || 0; document.getElementById('stat-unsure').textContent = l.unsure || 0; document.getElementById('stat-pain').textContent = l.pain || 0; document.getElementById('stat-happy').textContent = l.happy || 0; document.getElementById('stat-help').textContent = l.help || 0; if (isAdmin) { const a = d.allTime || {}; document.getElementById('admin-stat-done').textContent = a.done || 0; document.getElementById('admin-stat-unsure').textContent = a.unsure || 0; document.getElementById('admin-stat-pain').textContent = a.pain || 0; document.getElementById('admin-stat-happy').textContent = a.happy || 0; document.getElementById('admin-stat-help').textContent = a.help || 0; document.getElementById('admin-total-votes').textContent = a.total || 0; if (d.lapNumber !== undefined) document.getElementById('admin-lap-number').textContent = d.lapNumber; } }).catch(e => console.error(e)); }
    function updateAdminPanel() { if (!isAdmin) return; fetch(API + '?action=get_emoji_stats&t=' + Date.now()).then(r => r.json()).then(d => { if (!d.success) return; if (d.lapNumber !== undefined) document.getElementById('admin-lap-number').textContent = d.lapNumber; const a = d.allTime || {}; document.getElementById('admin-stat-done').textContent = a.done || 0; document.getElementById('admin-stat-unsure').textContent = a.unsure || 0; document.getElementById('admin-stat-pain').textContent = a.pain || 0; document.getElementById('admin-stat-happy').textContent = a.happy || 0; document.getElementById('admin-stat-help').textContent = a.help || 0; document.getElementById('admin-total-votes').textContent = a.total || 0; }).catch(e => console.error(e)); }
    function createSnapshot() { if (!isAdmin) return; if (!confirm('📸 Create snapshot of current words, sentences, and active users?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=create_snapshot' }).then(r => r.json()).then(d => { if (d.success) alert('✅ Snapshot created!'); else alert('❌ Failed to create snapshot'); }).catch(() => alert('❌ Network error')); }
    function resetCloud() { if (!isAdmin) return; if (!confirm('⚠️ Clear ALL current words and sentences?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=reset&type=cloud' }).then(r => r.json()).then(d => { if (d.success) { alert('✅ Cloud data cleared!'); renderCloud(); renderSentencesCloud(); } }).catch(() => alert('❌ Network error')); }
    function openExportModal() { if (!isAdmin) return; document.getElementById('export-modal').classList.remove('hidden'); loadSnapshotList(); }
    function loadSnapshotList() { fetch(API + '?action=get_snapshots').then(r => r.json()).then(d => { const c = document.getElementById('snapshot-list'); if (!d.success || !d.snapshots || !d.snapshots.length) { c.innerHTML = '<p style="color:#999;text-align:center; padding:20px;">No snapshots yet. Create one first!</p>'; return; } c.innerHTML = ''; d.snapshots.forEach(s => { const div = document.createElement('div'); div.className = 'snap-item'; div.innerHTML = `<input type="checkbox" class="snap-check" value="${s.id}" style="width:16px;height:16px;cursor:pointer;"><div class="snap-meta"><strong>${escapeHtml(s.class_name)} - ${s.timestamp}</strong><small>📝 ${s.word_count} words | 💬 ${s.sentence_count} sentences | 👥 ${s.active_users_count || 0} users</small></div>`; c.appendChild(div); }); document.getElementById('select-all-check').checked = false; document.querySelectorAll('.snap-check').forEach(cb => cb.checked = false); }); }
    function toggleSelectAll(m) { document.querySelectorAll('.snap-check').forEach(cb => cb.checked = m.checked); }
    function getSelectedIds() { const s = []; document.querySelectorAll('.snap-check:checked').forEach(cb => s.push(cb.value)); return s; }
    function performExportSelected() { const s = getSelectedIds(); if (!s.length) return alert('⚠️ Please select at least one snapshot.'); triggerDownloadForm({ action: 'export_snapshot', scope: 'selected', selected_ids: JSON.stringify(s) }); closeExportModal(); }
    function performExportAll() { triggerDownloadForm({ action: 'export_snapshot', scope: 'all', selected_ids: '[]' }); closeExportModal(); }
    function performDeleteSelected() { const s = getSelectedIds(); if (!s.length) return alert('⚠️ Please select at least one snapshot to delete.'); if (!confirm(`⚠️ Delete ${s.length} snapshot(s)?`)) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=delete_snapshot&selected_ids=' + encodeURIComponent(JSON.stringify(s)) }).then(r => r.json()).then(d => { if (d.success) { alert('✅ Deleted!'); loadSnapshotList(); } else alert('❌ ' + (d.message || 'Error')); }).catch(() => alert('❌ Network error')); }
    function triggerDownloadForm(fd) { const f = document.createElement('form'); f.method = 'POST'; f.action = API; f.style.display = 'none'; for (const k in fd) { const i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = fd[k]; f.appendChild(i); } document.body.appendChild(f); f.submit(); document.body.removeChild(f); alert('✅ Export started! Check downloads.'); }
    function closeExportModal() { document.getElementById('export-modal').classList.add('hidden'); }
    function toggleDeleteMode() { if (!isAdmin) return; deleteMode = !deleteMode; const b = document.getElementById('btn-delete-mode'), a = modulesConfig.sentences_cloud ? document.getElementById('sentences-container') : document.getElementById('cloud-container'); if (deleteMode) { b.classList.add('active'); b.textContent = '✅ Delete ON'; if (a) a.classList.add('admin-mode'); } else { b.classList.remove('active'); b.textContent = '🗑️ Delete'; if (a) a.classList.remove('admin-mode'); } }
    function toggleUsernames() { if (!isAdmin) return; showUsernamesMode = !showUsernamesMode; const b = document.getElementById('btn-usernames'), a = modulesConfig.sentences_cloud ? document.getElementById('sentences-container') : document.getElementById('cloud-container'); if (showUsernamesMode) { b.classList.add('active'); b.textContent = '👥 Users ON'; if (a) a.classList.add('show-usernames'); } else { b.classList.remove('active'); b.textContent = '👥 Users'; if (a) a.classList.remove('show-usernames'); } if (modulesConfig.wordcloud) renderCloud(); if (modulesConfig.sentences_cloud) renderSentencesCloud(); }
    function toggleEmojiLog() { if (!isAdmin) return; showEmojiLogMode = !showEmojiLogMode; const s = document.getElementById('emoji-log-section'), b = document.getElementById('btn-emoji-log'); if (showEmojiLogMode) { s.classList.remove('hidden'); s.classList.add('visible'); b.classList.add('active'); b.textContent = '📋 Log ON'; refreshEmojiLog(); } else { s.classList.remove('visible'); s.classList.add('hidden'); b.classList.remove('active'); b.textContent = '📋 Emoji Log'; } }
    function newLap() { if (!isAdmin || !confirm('🏁 Start new lap?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=reset_emoji&type=lap' }).then(r => r.json()).then(d => { if (d.success) { alert('✅ New lap #' + d.lap); updateAdminPanel(); } }).catch(e => console.error(e)); }
    function viewPdf() { if (!isAdmin) return; if (!modulesConfig.pdf_viewer) { modulesConfig.pdf_viewer = true; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=update_modules_config&wordcloud=' + modulesConfig.wordcloud + '&sentences_cloud=' + modulesConfig.sentences_cloud + '&pdf_viewer=true&emoji_meter=' + modulesConfig.emoji_meter + '&qr_link=' + modulesConfig.qr_link + '&info_text=' + modulesConfig.info_text }); document.getElementById('btn-module-pdf').classList.add('active'); } document.getElementById('module-pdf').classList.remove('hidden'); loadPdf(); document.getElementById('module-pdf').scrollIntoView({ behavior: 'smooth' }); }
    function uploadPdf() { if (!isAdmin) return; const i = document.createElement('input'); i.type = 'file'; i.accept = '.pdf'; i.onchange = () => { const f = i.files[0]; if (!f) return; const fd = new FormData(); fd.append('pdf', f); fetch(API + '?action=upload_pdf', { method: 'POST', body: fd }).then(async r => { const t = await r.text(); try { return JSON.parse(t); } catch(e) { return { success: false, error: t }; } }).then(d => { if (d.success) { alert('✅ Uploaded!'); if (!modulesConfig.pdf_viewer) { modulesConfig.pdf_viewer = true; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=update_modules_config&wordcloud=' + modulesConfig.wordcloud + '&sentences_cloud=' + modulesConfig.sentences_cloud + '&pdf_viewer=true&emoji_meter=' + modulesConfig.emoji_meter + '&qr_link=' + modulesConfig.qr_link + '&info_text=' + modulesConfig.info_text }); } currentPdfFilename = ''; pdfDoc = null; setTimeout(() => waitForPdfReady().then(viewPdf).catch(viewPdf), 300); } else alert('❌ ' + (d.error || d.message)); }).catch(() => alert('❌ Network error')); }; i.click(); }
    function deletePdf() { if (!isAdmin || !confirm('🗑️ Delete PDF?')) return; fetch(API + '?action=delete_pdf', { method: 'POST' }).then(r => r.json()).then(d => { if (d.success) { alert('✅ Deleted'); currentPdfFilename = ''; pdfDoc = null; document.getElementById('pdf-viewer').innerHTML = '<div class="no-pdf"><div><p style="font-size:48px;margin-bottom:20px;">📄</p><p>No lesson material uploaded yet</p></div></div>'; } }); }
    function generateQR() { const c = document.getElementById('qr-code'), l = document.getElementById('qr-link-display'); if (!c) return; const u = getCurrentModuleUrl(); l.textContent = u.replace(/^https?:\/\//, ''); l.href = u; c.innerHTML = ''; const w = Math.min(c.clientWidth || 220, 300); try { new QRCode(c, { text: u, width: w, height: w, correctLevel: QRCode.CorrectLevel.H }); } catch(e) { c.innerHTML = '<p style="color:#e74c3c">Error</p>'; } }
    function getCurrentModuleUrl() { return window.location.protocol + '//' + window.location.hostname + window.location.pathname.split('?')[0].split('#')[0]; }
    function renderCloud() { fetch(API + '?action=get_words').then(r => r.json()).then(d => { const c = document.getElementById('cloud-container'); if (!c) return; if (!d || !d.length) { c.innerHTML = '<p style="color:#999; text-align:center; padding:40px; font-size:22px;">No words yet. Be first! 👆</p>'; return; } c.innerHTML = ''; d.slice(0, 80).forEach((it, i) => { const w = document.createElement('div'); w.className = 'cloud-word-wrapper'; w.style.animationDelay = (i * 0.03) + 's'; const t = it.display || it.word, n = it.count || 1, u = it.users || []; let h = 0; for (let j = 0; j < t.length; j++) h = t.charCodeAt(j) + ((h << 5) - h); const col = COLOR_PALETTE[Math.abs(h) % COLOR_PALETTE.length]; const s = document.createElement('span'); s.className = 'cloud-word'; s.textContent = t; s.style.color = col; s.style.borderColor = col; const db = document.createElement('button'); db.className = 'delete-btn'; db.textContent = '×'; db.onclick = e => { e.stopPropagation(); deleteWord(it.word); }; const b = document.createElement('span'); b.className = 'count-badge'; b.textContent = n; const ui = document.createElement('div'); ui.className = 'user-indicator'; ui.textContent = u.length + ' user' + (u.length > 1 ? 's' : ''); const tt = document.createElement('div'); tt.className = 'username-tooltip'; tt.innerHTML = '<div class="names">' + [...new Set(u)].map(x => '<span>' + escapeHtml(x) + '</span>').join('') + '</div>'; w.append(db, s, b, ui, tt); c.appendChild(w); }); if (deleteMode) c.classList.add('admin-mode'); if (showUsernamesMode) c.classList.add('show-usernames'); }); }
    function submitWord() { const i = document.getElementById('word-input'), w = i ? i.value.trim() : ''; if (!w) return; const b = event.target; b.textContent = 'Sending...'; b.disabled = true; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=add_word&word=' + encodeURIComponent(w) + '&username=' + encodeURIComponent(username) }).then(() => { if (i) i.value = ''; renderCloud(); }).finally(() => { b.textContent = '🚀 Submit'; b.disabled = false; }); }
    function deleteWord(w) { if (!isAdmin || !confirm('Delete "' + w + '"?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=delete_word&word=' + encodeURIComponent(w) }).then(() => renderCloud()); }
    function renderSentencesCloud() { fetch(API + '?action=get_sentences').then(r => r.json()).then(d => { const c = document.getElementById('sentences-container'); if (!c) return; if (!d || !d.length) { c.innerHTML = '<p style="color:#999; text-align:center; padding:40px; font-size:22px;">No sentences yet. Be first! 👆</p>'; return; } c.innerHTML = ''; d.slice(0, 80).forEach((it, i) => { const w = document.createElement('div'); w.className = 'cloud-word-wrapper'; w.style.animationDelay = (i * 0.03) + 's'; const t = it.display || it.sentence, n = it.count || 1, u = it.users || []; let h = 0; for (let j = 0; j < t.length; j++) h = t.charCodeAt(j) + ((h << 5) - h); const col = COLOR_PALETTE[Math.abs(h) % COLOR_PALETTE.length]; const s = document.createElement('span'); s.className = 'cloud-sentence'; s.textContent = t; s.style.color = col; s.style.borderColor = col; const db = document.createElement('button'); db.className = 'delete-btn'; db.textContent = '×'; db.onclick = e => { e.stopPropagation(); deleteSentence(it.sentence); }; const b = document.createElement('span'); b.className = 'count-badge'; b.textContent = n; const ui = document.createElement('div'); ui.className = 'user-indicator'; ui.textContent = u.length + ' user' + (u.length > 1 ? 's' : ''); const tt = document.createElement('div'); tt.className = 'username-tooltip'; tt.innerHTML = '<div class="names">' + [...new Set(u)].map(x => '<span>' + escapeHtml(x) + '</span>').join('') + '</div>'; w.append(db, s, b, ui, tt); c.appendChild(w); }); if (deleteMode) c.classList.add('admin-mode'); if (showUsernamesMode) c.classList.add('show-usernames'); }); }
    function submitSentence() { const i = document.getElementById('sentence-input'), s = i ? i.value.trim() : ''; if (!s) return; const b = event.target; b.textContent = 'Sending...'; b.disabled = true; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=add_sentence&sentence=' + encodeURIComponent(s) + '&username=' + encodeURIComponent(username) }).then(() => { if (i) i.value = ''; renderSentencesCloud(); }).finally(() => { b.textContent = '🚀 Submit'; b.disabled = false; }); }
    function deleteSentence(s) { if (!isAdmin || !confirm('Delete this sentence?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=delete_sentence&sentence=' + encodeURIComponent(s) }).then(() => renderSentencesCloud()); }
    function resetEmoji(t) { if (!isAdmin || !confirm('Reset emoji data?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=reset_emoji&type=' + t }).then(() => { alert('✅ Reset!'); updateAdminPanel(); }); }
    function deleteEmojiLog(t, idx = -1) { if (!isAdmin) return; if (t === 'all' && !confirm('⚠️ Delete ALL?')) return; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=delete_emoji_log&type=' + t + (idx >= 0 ? '&index=' + idx : '') }).then(() => refreshEmojiLog()); }
    function refreshEmojiLog() { if (!showEmojiLogMode) return; fetch(API + '?action=get_emoji_log').then(r => r.json()).then(d => { const tb = document.getElementById('emoji-log-body'); if (!d.success || !d.log || !d.log.length) { tb.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No votes yet</td></tr>'; return; } tb.innerHTML = d.log.map((e, i) => `<tr><td class="col-emoji">${EMOJI_MAP[e.emoji] || e.emoji}</td><td class="col-user">${escapeHtml(e.username || 'Anonymous')}</td><td class="col-time">${new Date(e.time * 1000).toLocaleTimeString()}</td><td><span class="col-lap">${e.lap || 1}</span></td><td class="col-action"><button class="delete-row-btn" onclick="deleteEmojiLog('single', ${i})">🗑️</button></td></tr>`).join(''); }); }
    function submitEmoji(em) { const n = Date.now(); if (n - lastVoteTime < 60000) return alert('Wait ' + Math.ceil((60000 - (n - lastVoteTime)) / 1000) + 's'); const b = document.getElementById('btn-emoji-' + em); if (!b) return; b.classList.add('disabled'); b.disabled = true; lastVoteTime = n; fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=emoji_vote&emoji=' + em + '&username=' + encodeURIComponent(username) }).then(() => updateEmojiStats()); setTimeout(() => { b.classList.remove('disabled'); b.disabled = false; }, 60000); }
    function updateUserCount() { fetch(API + '?action=get_user_count').then(r => r.json()).then(d => { const e = document.getElementById('user-count-display'); if (e && d.success) e.textContent = '👥 ' + d.label; }); }
    function checkEmojiAnimation() { fetch(API + '?action=get_emoji_animation').then(r => r.json()).then(d => { if (d.emoji) showEmojiAnimation(d.emoji); }); }
    function showEmojiAnimation(em) { const o = document.getElementById('emoji-overlay'), s = EMOJI_MAP[em] || em; for (let i = 0; i < 5; i++) setTimeout(() => { const e = document.createElement('div'); e.className = 'floating-emoji'; e.textContent = s; e.style.left = (Math.random() * 80 + 10) + '%'; o.appendChild(e); setTimeout(() => e.remove(), 3000); }, i * 200); o.classList.add('active'); setTimeout(() => o.classList.remove('active'), 3500); }
    function logUserLogin(u) { fetch(API, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=log_user_login&username=' + encodeURIComponent(u) }).then(() => updateUserCount()); }
    function saveUsername() { const i = document.getElementById('username-input'); if (i && i.value.trim()) { username = i.value.trim(); localStorage.setItem('eduUsername', username); document.getElementById('user-badge').textContent = '👤 ' + username; document.getElementById('login-modal').classList.add('hidden'); logUserLogin(username); } }
    function showLogin() { document.getElementById('login-modal').classList.remove('hidden'); document.getElementById('username-input').value = ''; }
    function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    document.addEventListener('keypress', e => { if (e.key === 'Enter') { const m = document.getElementById('login-modal'); if (!m.classList.contains('hidden')) saveUsername(); else submitWord(); } });
    document.getElementById('info-text-modal')?.addEventListener('click', e => { if (e.target === this) closeInfoTextEditor(); });
    document.getElementById('login-modal')?.addEventListener('click', e => { if (e.target === this) document.getElementById('login-modal').classList.add('hidden'); });
    document.getElementById('export-modal')?.addEventListener('click', e => { if (e.target === this) closeExportModal(); });
    document.getElementById('class-name-modal')?.addEventListener('click', e => { if (e.target === this) closeClassNameModal(); });
    document.getElementById('pdf-pin-modal')?.addEventListener('click', e => { if (e.target === this) closePdfPinModal(); });
</script>
</body>
</html>