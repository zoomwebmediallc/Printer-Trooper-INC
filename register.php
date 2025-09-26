<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/User.php';

redirectIfLoggedIn();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = '';
$form_data = [];
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : ($_POST['redirect'] ?? '');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data['username'] = trim($_POST['username'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $form_data['first_name'] = trim($_POST['first_name'] ?? '');
    $form_data['last_name'] = trim($_POST['last_name'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['address'] = trim($_POST['address'] ?? '');
    
    // Validation
    if(empty($form_data['username']) || empty($form_data['email']) || 
       empty($form_data['password']) || empty($form_data['first_name']) || 
       empty($form_data['last_name'])) {
        $error = 'Please fill in all required fields.';
    } elseif(strlen($form_data['username']) < 3) {
        $error = 'Username must be at least 3 characters long.';
    } elseif(!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif(strlen($form_data['password']) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif($form_data['password'] !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username exists
        $user->username = $form_data['username'];
        if($user->usernameExists()) {
            $error = 'Username already exists. Please choose a different one.';
        } else {
            // Check if email exists
            $user->email = $form_data['email'];
            if($user->emailExists()) {
                $error = 'Email already exists. Please use a different email address.';
            } else {
                // Register user
                $user->username = $form_data['username'];
                $user->email = $form_data['email'];
                $user->password = $form_data['password'];
                $user->first_name = $form_data['first_name'];
                $user->last_name = $form_data['last_name'];
                $user->phone = $form_data['phone'];
                $user->address = $form_data['address'];
                
                if($user->register()) {
                    $target = 'login.php?registered=1' . (!empty($redirect) ? ('&redirect=' . urlencode($redirect)) : '');
                    header("Location: $target");
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="auth-container" style="max-width: 500px;">
            <h2 style="text-align: center; margin-bottom: 1.5rem; color: #2c3e50;">Register</h2>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php<?php echo !empty($redirect) ? ('?redirect=' . urlencode($redirect)) : ''; ?>">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name: *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name: *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username: *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" required>
                    <small style="color: #6c757d;">At least 3 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email: *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="password">Password: *</label>
                        <input type="password" id="password" name="password" required>
                        <small style="color: #6c757d;">At least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password: *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Register</button>
                    <p style="text-align: center; margin-top: 1rem;">
                        Already have an account? <a href="login.php<?php echo !empty($redirect) ? ('?redirect=' . urlencode($redirect)) : ''; ?>" style="color: #3498db;">Login here</a>
                    </p>
                </div>
                
                <p style="text-align: center; font-size: 0.9rem; color: #6c757d; margin-top: 1rem;">
                    * Required fields
                </p>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Printer Trooper Inc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>