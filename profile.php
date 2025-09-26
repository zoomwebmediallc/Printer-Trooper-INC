<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/User.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$userId = getUserId();
if (!$userModel->getById($userId)) {
    // Unexpected: user not found
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($first_name === '' || $last_name === '') {
        $error = 'First and Last name are required.';
    } else {
        $userModel->id = $userId;
        $userModel->first_name = $first_name;
        $userModel->last_name = $last_name;
        $userModel->phone = $phone;
        $userModel->address = $address;

        if ($userModel->updateProfile()) {
            $success = 'Profile updated successfully.';
            // Refresh model for latest values
            $userModel->getById($userId);
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account - Printer Trooper Inc</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="profile-wrapper">
            <h1 class="page-title">My Account</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="profile-header-card">
                <div class="avatar-lg">
                    <?php
                        $fn = trim($userModel->first_name ?? '');
                        $ln = trim($userModel->last_name ?? '');
                        $initials = strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1));
                        echo htmlspecialchars($initials !== '' ? $initials : 'U');
                    ?>
                </div>
                <div class="meta">
                    <div class="name">
                        <?php echo htmlspecialchars(($userModel->first_name ?? '') . ' ' . ($userModel->last_name ?? '')); ?>
                    </div>
                    <div class="email">
                        <?php echo htmlspecialchars($userModel->email ?? ''); ?>
                    </div>
                </div>
            </div>

            <div class="auth-container profile-form-card">
                <form method="POST" action="profile.php">
                    <div class="profile-grid-2">
                        <div class="form-group input-with-icon">
                            <i class="fa-solid fa-user"></i>
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($userModel->first_name ?? ''); ?>" />
                        </div>
                        <div class="form-group input-with-icon">
                            <i class="fa-solid fa-user"></i>
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($userModel->last_name ?? ''); ?>" />
                        </div>
                    </div>

                    <div class="form-group input-with-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <label for="email">Email (read-only)</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($userModel->email ?? ''); ?>" disabled />
                    </div>

                    <div class="form-group input-with-icon">
                        <i class="fa-solid fa-phone"></i>
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($userModel->phone ?? ''); ?>" />
                    </div>

                    <div class="form-group input-with-icon address">
                        <i class="fa-solid fa-location-dot"></i>
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($userModel->address ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions profile-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </main>
</body>
</html>

