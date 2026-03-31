<!-- File 4 of 8: index.php - COMPLETE SIMPLIFIED -->
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
            <!-- Word Cloud Tool - Redirects to wordcloud.php -->
            <div class="tool-card" onclick="window.location.href='wordcloud.php'">
                <h3>☁️ Word Cloud</h3>
                <p>Share words with the class</p>
            </div>
            
            <!-- Satisfaction Tool -->
            <div class="tool-card" onclick="App.loadTool('satisfaction')">
                <h3>😊 Satisfaction</h3>
                <p>Rate today's session</p>
            </div>
            
            <!-- Admin Tool -->
            <div class="tool-card" onclick="App.checkAdmin()">
                <h3>🔒 Admin</h3>
                <p>Teacher access only</p>
            </div>
        </div>
        
        <br>
        <button class="secondary" onclick="App.logout()">Change Name</button>
    </section>

    <!-- 3. Satisfaction Tool -->
    <section id="screen-satisfaction" class="screen hidden">
        <h2>😊 How was it?</h2>
        <p style="color: #666;">Tap to rate the session</p>
        <div class="emoji-container">
            <button class="emoji-btn" onclick="Satisfaction.submit(1)" aria-label="Very Bad">😡</button>
            <button class="emoji-btn" onclick="Satisfaction.submit(2)" aria-label="Bad">😕</button>
            <button class="emoji-btn" onclick="Satisfaction.submit(3)" aria-label="Okay">😐</button>
            <button class="emoji-btn" onclick="Satisfaction.submit(4)" aria-label="Good">🙂</button>
            <button class="emoji-btn" onclick="Satisfaction.submit(5)" aria-label="Excellent">😍</button>
        </div>
        <div class="stats-box">
            <span style="font-size: 16px; color: #666;">Class Average:</span><br>
            <span id="avg-score">-</span><span style="font-size: 20px;">/5</span>
        </div>
        <button class="secondary" onclick="App.showScreen('screen-list')">← Back</button>
    </section>

    <!-- 4. Admin Login -->
    <section id="screen-admin-login" class="screen hidden">
        <h2>🔒 Admin Access</h2>
        <p style="color: #666;">Teacher password required</p>
        <input type="password" id="admin-pass" placeholder="Password" autocomplete="current-password">
        <br>
        <button onclick="Admin.login()">Login</button>
        <br>
        <button class="secondary" onclick="App.showScreen('screen-list')">← Back</button>
    </section>

    <!-- 5. Admin Dashboard -->
    <section id="screen-admin-dash" class="screen hidden">
        <h2>⚙️ Dashboard</h2>
        <p style="color: #666;">Reset tools for new class</p>
        <button style="background: var(--danger);" onclick="Admin.reset('words')">🗑️ Reset Word Cloud</button>
        <br>
        <button style="background: var(--danger);" onclick="Admin.reset('votes')">🗑️ Reset Satisfaction</button>
        <br>
        <button class="secondary" onclick="App.showScreen('screen-list')">← Logout</button>
    </section>
</div>

<script src="js/app.js"></script>
<script src="js/satisfaction.js"></script>
<script src="js/admin.js"></script>

</body>
</html>