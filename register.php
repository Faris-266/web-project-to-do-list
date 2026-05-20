<?php
include "main.php"; // connects to DB via $pdo

$status_message = "";

if (isset($_POST['submit'])) {
    // Sanitize and format
    $fname = ucfirst(strtolower(trim($_POST['fname'])));
    $lname = ucfirst(strtolower(trim($_POST['lname'])));
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $status_message = "Passwords do not match!";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM taskify_users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->rowCount() > 0) {
                $status_message = "Email is already used!";
            } else {
                // Check if username already exists
                $stmt2 = $pdo->prepare("SELECT id FROM taskify_users WHERE username = :username");
                $stmt2->execute([':username' => $username]);
                if ($stmt2->rowCount() > 0) {
                    $status_message = "Username is already taken!";
                } else {
                    // Insert new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_stmt = $pdo->prepare(
                        "INSERT INTO taskify_users (fname, lname, username, email, password) 
                         VALUES (:fname, :lname, :username, :email, :password)"
                    );
                    $insert_stmt->execute([
                        ':fname' => $fname,
                        ':lname' => $lname,
                        ':username' => $username,
                        ':email' => $email,
                        ':password' => $hashed_password
                    ]);

                    // Redirect to login page
                    header("Location: login.php");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $status_message = "Something went wrong: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Taskify</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Plus Jakarta Sans',sans-serif; background:#00a884; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
.auth-container { background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.15); width:100%; max-width:420px; padding:40px; }
.auth-header{text-align:center;margin-bottom:30px;}
.logo-text{font-size:2.2rem;font-weight:800;color:#00a884;margin-bottom:5px;display:inline-block;}
.logo-text span{color:#1e2139;}
.auth-header h1{font-size:1.5rem;color:#1e2139;margin-bottom:8px;font-weight:700;}
.auth-header p{font-size:0.95rem;color:#64748b;}
.status-msg{text-align:center;color:#1e2139;font-weight:bold;margin-bottom:15px;padding:10px;background:#f1f5f9;border-radius:10px;}
.form-group{margin-bottom:18px;display:flex;flex-direction:column;}
label{display:block;font-weight:600;margin-bottom:8px;color:#1e2139;font-size:0.9rem;}
input[type=text],input[type=email],input[type=password]{width:100%;padding:12px 16px;border:2px solid #f1f5f9;border-radius:10px;font-size:1rem;background:#f8fafc;}
input:focus{border-color:#00a884;outline:none;background-color:#fff;}
.checkbox-wrapper{display:flex;align-items:center;margin-top:8px;gap:10px;font-size:0.9rem;color:#64748b;cursor:pointer;user-select:none;}
.checkbox-wrapper input{width:16px;height:16px;cursor:pointer;accent-color:#00a884;}
.btn{width:100%;padding:14px;margin-top:10px;background-color:#00a884;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:transform 0.2s, background 0.3s;}
.btn:hover{background-color:#008f6f;transform:translateY(-1px);}
.terms{font-size:0.8rem;color:#94a3b8;margin-top:20px;text-align:center;}
.terms a{color:#00a884;text-decoration:none;font-weight:600;}
.auth-footer{text-align:center;margin-top:25px;font-size:0.9rem;color:#64748b;}
.auth-footer a{color:#00a884;text-decoration:none;font-weight:700;}
@media(max-width: 480px) {
    body { padding: 15px; }
    .auth-container { padding: 25px 20px; width: 100%; border-radius: 15px; }
    .logo-text { font-size: 1.8rem; }
    .auth-header h1 { font-size: 1.35rem; }
    .form-group input { padding: 10px 14px; }
}
</style>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <div class="logo-text">Taskify<span>.</span></div>
        <h1>Create your account</h1>
        <p>Join Taskify and stay organized</p>
    </div>

    <?php if($status_message != ""): ?>
        <div class="status-msg"><?php echo $status_message; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return handleRegister()">
        <div class="form-group">
            <label for="fname">First Name</label>
            <input type="text" name="fname" id="fname" placeholder="First Name" required>
        </div>

        <div class="form-group">
            <label for="lname">Last Name</label>
            <input type="text" name="lname" id="lname" placeholder="Last Name" required>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Username" required>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" name="email" id="email" placeholder="name@company.com" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <div class="checkbox-wrapper">
                <input type="checkbox" id="toggle-btn" onclick="toggleVisibility()">
                <label for="toggle-btn" style="margin-bottom:0;font-weight:normal;cursor:pointer;">👁️ Show Password</label>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm-password" placeholder="••••••••" required>
            <div class="checkbox-wrapper">
                <input type="checkbox" id="toggle2-btn" onclick="confirmToggleVisibility()">
                <label for="toggle2-btn" style="margin-bottom:0;font-weight:normal;cursor:pointer;">👁️ Show Password</label>
            </div>
        </div>

        <button type="submit" name="submit" class="btn">Get Started</button>
        <div class="terms">By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</div>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
    </div>
</div>

<script>
function toggleVisibility() {
    var pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}

function confirmToggleVisibility() {
    var pass = document.getElementById("confirm-password");
    pass.type = pass.type === "password" ? "text" : "password";
}

function handleRegister() {
    var fname = document.getElementById('fname').value.trim();
    var lname = document.getElementById('lname').value.trim();
    var username = document.getElementById('username').value.trim();
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm-password').value;

    // First Name & Last Name: letters only
    var namePattern = /^[A-Za-z]+$/;
    if (!namePattern.test(fname)) {
        alert("First Name must contain letters only.");
        document.getElementById('fname').focus();
        return false;
    }
    if (!namePattern.test(lname)) {
        alert("Last Name must contain letters only.");
        document.getElementById('lname').focus();
        return false;
    }

    // Username: letters, numbers, underscore
    var usernamePattern = /^[A-Za-z0-9_]{3,20}$/;
    if (!usernamePattern.test(username)) {
        alert("Username must be 3-20 characters, letters/numbers/underscore only.");
        document.getElementById('username').focus();
        return false;
    }

    // Email format check
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Enter a valid email address.");
        document.getElementById('email').focus();
        return false;
    }

    // Password rules
    if (password.length < 8) {
        alert("Password must be at least 8 characters.");
        document.getElementById('password').focus();
        return false;
    }
    var complexityCheck = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/;
    if (!complexityCheck.test(password)) {
        alert("Password must include at least 1 letter, 1 number, and 1 special character (!@#$%^&*).");
        document.getElementById('password').focus();
        return false;
    }

    // Password match
    if (password !== confirm) {
        alert("Passwords do not match.");
        document.getElementById('password').focus();
        return false;
    }

    return true;
}
</script>

</body>
</html>
