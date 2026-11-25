<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Errors are logged, never shown (this page is public).
ini_set('display_errors', IS_LOCAL ? '1' : '0');

$error = "";

if (Security::CheckBlockIP() > 0) {
    header("Location: forbidden.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    // Brute-force protection (same rules as the main login): previous failures in 15 minutes
    $failedUser = Security::totalloginfaileduser();
    $failedIp   = Security::totalloginfailed();
    $loginFailed = function () use (&$error, $failedUser, $failedIp) {
        Log::loginlogs("Failed");
        if ($failedIp >= 19) {
            Security::IPBlock();
            header("Location: forbidden.php");
            exit;
        }
        if ($failedUser >= 2) {
            Security::UserBlock(); // 3rd wrong password locks the account (admin can unblock)
            $error = "Your account is blocked after repeated wrong passwords. Please contact support.";
            return;
        }
        $error = "Invalid username or password.";
    };

    if (empty($username) || empty($password)) {
        $error = "Username and Password are required.";
    } else {
        $stmt = mysqli_prepare($connect, "SELECT * FROM user WHERE user = ? AND active = '1' LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);

                // Check user blocklist
                $escaped_user = mysqli_real_escape_string($connect, $username);
                $check_block = mysqli_query($connect, "SELECT * FROM user_blocklist WHERE username = '$escaped_user'");
                if ($check_block && mysqli_num_rows($check_block) > 0) {
                    $error = "Your account is blocked. Please contact support.";
                } else {
                    // Verify password (supports MD5 legacy and bcrypt)
                    $password_valid = false;
                    if ($user['pass'] === md5($password)) {
                        $password_valid = true;
                    } elseif (password_verify($password, $user['pass'])) {
                        $password_valid = true;
                    }

                    if ($password_valid) {
                        // Set all session variables for entire application
                        $_SESSION['user_id']       = $user['id'];
                        $_SESSION['us_id']         = $user['id'];
                        $_SESSION['user']          = $user['user'];
                        $_SESSION['user_name']     = $user['user'];
                        $_SESSION['level']         = $user['level'];
                        $_SESSION['userip']        = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
                        $_SESSION['sess_username'] = $user['user'];
                        $_SESSION['sess_uservalid']= 1; // (plaintext password no longer kept in the session)
                        session_regenerate_id(true);    // no session fixation
                        Log::loginlogs("Success");

                        // Check redirect destination
                        $redirect = $_GET['redirect'] ?? '';
                        if ($redirect === 'edit' || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.html') !== false)) {
                            header("Location: edit.php");
                            exit;
                        }

                        // Role-based redirection
                        if (intval($user['level']) === 5) {
                            header("Location: admin/home.php");
                        } else {
                            header("Location: customer/home.php");
                        }
                        exit;
                    } else {
                        $loginFailed();
                    }
                }
            } else {
                $loginFailed(); // same message as a wrong password: no username enumeration
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database query error: " . mysqli_error($connect);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Midhub360</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f6f9; }
        .login-box { max-width: 400px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .login-box h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .form-group input:focus { border-color: #4CAF50; outline: none; }
        button { width: 100%; padding: 12px; background: #4CAF50; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #45a049; }
        .error { color: #d9534f; background: #fdf7f7; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ebccd1; font-size: 14px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #007bff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Midhub360 Login</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus placeholder="Enter your username">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit">Log In</button>
    </form>
    <div class="links">
        <a href="index.php">Go to Main Portal Login</a>
    </div>
</div>
</body>
</html>
