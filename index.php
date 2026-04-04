<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>EduLite - Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🎓 EduLite</h1>
        
        <!-- Student Login -->
        <div id="screen-login" class="screen">
            <p>Enter your name to continue</p>
            <input type="text" id="username-input" placeholder="Your Name" maxlength="30" autocomplete="off">
            <button onclick="App.saveUsername()">Join Class</button>
            <br>
            <button class="secondary" onclick="App.checkAdmin()">🔒 Admin Access</button>
        </div>

        <!-- Welcome / Redirect to Unified Modules -->
        <div id="screen-list" class="screen hidden">
            <h2>Welcome, <span id="display-username"></span>! 👋</h2>
            <p>All classroom tools are combined in one unified view.</p>
            <button onclick="window.location.href='modules.php'" style="width:100%; max-width:400px; padding:18px; font-size:18px; margin: 15px 0;">
                🚀 Open Classroom Modules
            </button>
            <br>
            <button class="secondary" onclick="App.logout()">🚪 Change Name</button>
            <button class="secondary" onclick="App.checkAdmin()">🔒 Admin</button>
        </div>

        <!-- Admin Login -->
        <div id="screen-admin-login" class="screen hidden">
            <h2>🔒 Teacher Login</h2>
            <input type="password" id="admin-pass" placeholder="Password">
            <button onclick="Admin.login()">Login</button>
            <button class="secondary" onclick="App.showScreen('screen-list')">← Back</button>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>