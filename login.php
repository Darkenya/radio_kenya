<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

// Redirect already logged-in users
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Check if configuration is available (assuming config.php works)
    if (!function_exists('getDBConnection')) {
        $error = 'Database configuration not found.';
    } else {
        $conn = getDBConnection();
        
        // 1. Find the admin user by username or email
        $stmt = $conn->prepare("SELECT id, password, full_name, role, is_active FROM admin_users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        if ($user && $user['is_active'] == 1) {
            // 2. Verify the password hash
            if (password_verify($password, $user['password'])) {
                // 3. Login successful - create session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $username;
                // Use the user's full name for a friendlier greeting
                $_SESSION['admin_full_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Redirect to the protected admin panel
                header('Location: admin.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password, or account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Radio Kenya</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 30px; font-size: 2em; color: #667eea; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1a202c; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1em; }
        .btn-primary { width: 100%; padding: 15px; border: none; border-radius: 10px; font-size: 1em; font-weight: 600; cursor: pointer; transition: all 0.3s; background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        .alert-error { padding: 10px; background: #fed7d7; color: #742a2a; border-radius: 10px; margin-bottom: 15px; border: 2px solid #f56565; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>📻 Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">➡️ Log In</button>
        </form>
    </div>
</body>
</html>