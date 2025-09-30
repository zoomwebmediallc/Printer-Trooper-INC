<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Cart.php';

redirectIfLoggedIn();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$cart = new Cart($db);

$error = '';
$success = '';
// Sanitize redirect to avoid double-encoding and open redirects
function safe_redirect_target($val) {
    $raw = trim((string)$val);
    if ($raw === '') return '';
    // decode once if encoded
    $raw = urldecode($raw);
    // only allow same-site paths
    if (strpos($raw, '/printer-store/') !== 0) return '';
    return $raw;
}

$incomingRedirect = $_GET['redirect'] ?? '';
$redirectTarget = safe_redirect_target($incomingRedirect);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = safe_redirect_target($_POST['redirect'] ?? '');

    if(empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if($user->login($username, $password)) {
            // Persist user session
            loginUser($user);
            // Elevate to admin if allowed
            loginAdminIfAllowed();
            // Merge any session cart into this user's cart
            $cart->mergeSessionCartToUser(getUserId());
            // Redirect back to requested page if provided
            $target = !empty($redirect) ? $redirect : 'index.php';
            header("Location: $target");
            exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}

if(isset($_GET['registered'])) {
    $success = 'Registration successful! Please login with your credentials.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #2c3e50;">Login</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php if (!empty($redirectTarget)): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>" />
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Login</button>
                    <p style="text-align: center; margin-top: 1rem;">
                        Don't have an account? <a href="register.php<?php echo !empty($redirectTarget) ? ('?redirect=' . urlencode($redirectTarget)) : ''; ?>" style="color: #3498db;">Register here</a>
                    </p>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Printer Trooper Inc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>