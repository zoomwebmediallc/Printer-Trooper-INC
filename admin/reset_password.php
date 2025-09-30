<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$resetModel = new PasswordReset($db);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$token = trim($method === 'POST' ? ($_POST['token'] ?? '') : ($_GET['token'] ?? ''));
$valid = false;
$error = '';
$info = '';

// Only validate GET token presence on initial GET request
if ($method === 'GET') {
  if ($token !== '') {
    $row = $resetModel->verifyToken($token);
    $valid = (bool)$row;
    $resetRow = $row ?: null;
  } else {
    $error = 'Invalid or missing token.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // token already taken from POST above
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';
  $row = $resetModel->verifyToken($token);
  if (!$row) {
    $error = 'This reset link is invalid or has expired.';
  } else if ($password === '' || $confirm === '') {
    $error = 'Please enter and confirm your new password.';
  } else if ($password !== $confirm) {
    $error = 'Passwords do not match.';
  } else if (strlen($password) < 8) {
    $error = 'Password must be at least 8 characters.';
  } else {
    $userId = (int)$row['user_id'];
    if ($userModel->updatePassword($userId, $password)) {
      $resetModel->consumeToken($token);
      $info = 'Your password has been reset. You can now log in.';
      // Invalidate any active session
      logoutUser();
      // Show success and link to login
      $valid = false;
      // Ensure no stale error remains
      $error = '';
    } else {
      $error = 'Failed to update password. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Reset Password</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style>
    .auth-container { max-width: 420px; margin: 60px auto; background: #fff; padding: 24px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);} 
    .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 12px; }
    .alert-info { background: #e7f3fe; color: #084298; }
    .alert-error { background: #fde2e2; color: #7d1d1d; }
    .form-group { margin-bottom: 12px; }
    .form-group label { display:block; margin-bottom: 6px; font-weight: 600; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
    .form-actions { margin-top: 12px; display:flex; align-items:center; gap:12px; }
    .btn-primary { background:#084298; color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; }
    .hint { font-size: 13px; display:flex; align-items:center; justify-content:center; }
  </style>
</head>
<body>
  <main class="main-content">
    <div class="auth-container">
      <h2 style="text-align:center;color:#084298;">Admin - Reset Password</h2>

      <?php if (!empty($info)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($info); ?></div>
        <p class="hint"><a href="/printer-store/admin/login.php">Back to login</a></p>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($valid): ?>
      <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm" required />
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Set new password</button>
        </div>
      </form>
      <?php else: ?>
        <?php if (empty($info)): ?>
          <p class="hint"><a href="/printer-store/admin/forgot_password.php">Request a new reset link</a></p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
