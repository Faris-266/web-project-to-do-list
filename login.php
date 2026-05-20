<?php
session_start();
include "main.php"; // $pdo from main.php

$login_message = "";

if (isset($_POST['submit'])) {
    $user_input = trim($_POST['user_input']); // email or username
    $password = $_POST['password'];

    try {
        // SQL with two placeholders: email OR username
        $stmt = $pdo->prepare("SELECT id, username, email, password, fname FROM taskify_users WHERE email = :email OR username = :username");
        $stmt->execute([
            ':email' => $user_input,
            ':username' => $user_input
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify hashed password
            if (password_verify($password, $user['password'])) {
                // Login successful → set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['fname'] = $user['fname'];

                header("Location: dashboard.php");
                exit();
            } else {
                $login_message = "Incorrect password!";
            }
        } else {
            $login_message = "No account found with that email or username!";
        }

    } catch (PDOException $e) {
        $login_message = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Taskify</title>
<link rel="icon" type="image/png" href="images/n_logo.png"> 
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* Your existing CSS from before */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#00a884;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.auth-container{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.15);width:100%;max-width:420px;padding:40px;}
.auth-header{text-align:center;margin-bottom:30px;}
.logo-link{display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:1.8rem;font-weight:800;color:#008f6f;letter-spacing:-0.5px;margin-bottom:15px;}
.auth-header h1{font-size:1.7rem;color:#1e2139;margin-bottom:8px;}
.auth-header p{font-size:0.95rem;color:#64748b;}
.status-msg{text-align:center;color:#1e2139;font-weight:bold;margin-bottom:15px;padding:10px;background:#f1f5f9;border-radius:10px;}
.form-group{margin-bottom:18px;}
label{display:block;font-weight:600;margin-bottom:8px;color:#1e2139;font-size:0.9rem;}
input:not([type="checkbox"]){width:100%;padding:12px 16px;border:2px solid #f1f5f9;border-radius:10px;font-size:1rem;background:#f8fafc;transition:all 0.3s ease;}
input:focus{border-color:#00a884;outline:none;background-color:#fff;box-shadow:0 0 0 4px rgba(0,168,132,0.1);}
.form-actions{display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;margin:15px 0;color:#64748b;}
.remember-wrapper{display:flex;align-items:center;gap:8px;cursor:pointer;}
input[type="checkbox"]{width:18px;height:18px;accent-color:#00a884;cursor:pointer;}
.remember-wrapper label{margin-bottom:0;cursor:pointer;font-weight:400;color:#64748b;}
.form-actions a{color:#00a884;text-decoration:none;font-weight:600;}
.btn{width:100%;padding:14px;margin-top:10px;background-color:#00a884;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background 0.3s,transform 0.2s;}
.btn:hover{background-color:#008f6f;transform:translateY(-1px);}
.auth-footer{text-align:center;margin-top:25px;font-size:0.9rem;color:#64748b;}
.auth-footer a{color:#00a884;font-weight:700;text-decoration:none;}
.checkbox-wrapper{display:flex;align-items:center;margin-top:8px;gap:10px;font-size:0.9rem;color:#64748b;cursor:pointer;user-select:none;}
.checkbox-wrapper input{width:16px;height:16px;cursor:pointer;accent-color:#00a884;}
@media(max-width: 480px) {
    body { padding: 15px; }
    .auth-container { padding: 25px 20px; width: 100%; }
    .auth-header h1 { font-size: 1.5rem; }
    .logo-link { font-size: 1.5rem; }
}
</style>
</head>
<body>

<div class="auth-container">
   <div class="auth-header">
        <a href="project.html" class="logo-link">Taskify</a>
        <h1>Welcome back</h1>
        <p>Sign in to continue to Taskify</p>
    </div>

    <?php if($login_message != ""): ?>
        <div class="status-msg"><?php echo $login_message; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return handleLogin()">
       <div class="form-group">
            <label for="user_input">Email or Username</label>
            <input type="text" name="user_input" id="user_input" placeholder="Email or Username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <div class="checkbox-wrapper">
                <input type="checkbox" id="toggle-btn" onclick="toggleVisibility()">
                <label for="toggle-btn" style="margin-bottom:0;font-weight:normal;cursor:pointer;">👁️ Show Password</label>
            </div>
        </div>

        <div class="form-actions">
            <div class="remember-wrapper">
                <input type="checkbox" id="remember">
                <label for="remember">Remember me</label>
            </div>
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit" name="submit" class="btn">Sign In</button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="register.php">Create one</a>
    </div>
</div>

<script>
function toggleVisibility() {
    var pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

function handleLogin() {
    var user_input = document.getElementById('user_input').value.trim();
    var password = document.getElementById('password').value;

    if(user_input === "" || password === "") {
        alert("Please fill in all fields.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
