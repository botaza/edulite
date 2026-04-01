<!-- File 4 of 8: index.php - WITH MODULES BUTTON -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>EduLite Tools</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="apple-touch-icon" href="image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<div class="container">
    <h1>🎓 EduLite</h1>

    <!-- 1. Username Login -->
    <section id="screen-login" class="screen">
        <h2>Enter Your Name</h2>
        <p style="color: #666; margin-bottom: 20px;">Join the class to participate in activities</p>
        <input type="text" id="username-input" placeholder="Your name" autocomplete="name" autocapitalize="words">
        <br>
        <button onclick="App.saveUsername()">Join Class</button>
    </section>

    <!-- 2. Tool List -->
    <section id="screen-list" class="screen hidden">
        <h2>Select a Tool</h2>
        <p style="color: #666; margin: 10px 0;">Hi, <span id="display-username" style="font-weight: bold; color: var(--primary);"></span>!</p>
        
        <div class="tool-grid">
            <!-- Modules App (Unified) -->
            <div class="tool-card" onclick="window.location.href='modules.php'">
                <h3>🎯 Modules</h3>
                <p>Unified classroom tools</p>
            </div>
            
            <!-- Word Cloud (Legacy) -->
            <div class="tool-card" onclick="window.location.href='wordcloud.php'">
                <h3>☁️ Word Cloud</h3>
                <p>Share words with the class</p>
            </div>
            
            <!-- Lesson Mode (PDF + Emoji) -->
            <div class="tool-card" onclick="window.location.href='lesson.php'">
                <h3>📖 Lesson Mode</h3>
                <p>View PDF + give feedback</p>
            </div>
            
            <!-- Admin Tool -->
            <div class="tool-card" onclick="App.checkAdmin()">
                <h3>🔒 Admin</h3>
                <p>Teacher access only</p>
            </div>
        </div>
    </section>

    <!-- 3. Admin Login -->
    <section id="screen-admin-login" class="screen hidden">
        <h2>🔒 Admin Access</h2>
        <p style="color: #666;">Teacher password required</p>
        <input type="password" id="admin-pass" placeholder="Password" autocomplete="current-password">
        <br>
        <button onclick="Admin.login()">Login</button>
        <br>
        <button class="secondary" onclick="App.showScreen('screen-list')">← Back</button>
    </section>

    <!-- 4. Admin Dashboard -->
    <section id="screen-admin-dash" class="screen hidden">
        <h2>⚙️ Dashboard</h2>
        <p style="color: #666;">Manage tools and lessons</p>
        
        <h3 style="margin-top: 20px; color: #667eea;">🎯 Modules</h3>
        <button onclick="Admin.toggleModules()">🔧 Configure Modules</button>
        
        <h3 style="margin-top: 20px; color: #667eea;">📖 Lesson Mode</h3>
        <button onclick="Admin.uploadPdf()">📤 Upload PDF</button>
        <br>
        <button class="secondary" onclick="Admin.deletePdf()">🗑️ Delete PDF</button>
        
        <h3 style="margin-top: 20px; color: #667eea;">🗑️ Reset Tools</h3>
        <button style="background: var(--danger);" onclick="Admin.reset('words')">Reset Word Cloud</button>
        <br>
        <button class="secondary" onclick="App.showScreen('screen-list')">← Back</button>
    </section>
</div>

<!-- CRITICAL: Load app.js FIRST, then admin.js -->
<script src="js/app.js"></script>
<script src="js/admin.js"></script>

</body>
</html>