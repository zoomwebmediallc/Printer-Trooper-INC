<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/PasswordReset.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$resetModel = new PasswordReset($db);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$token = trim($method === 'POST' ? ($_POST['token'] ?? '') : ($_GET['token'] ?? ''));
$valid = false;
$error = '';
$info = '';

// Only validate token presence on initial GET request
if ($method === 'GET') {
  if ($token !== '') {
    $row = $resetModel->verifyToken($token);
    $valid = (bool) $row;
    $resetRow = $row ?: null;
  } else {
    $error = 'Invalid or missing token.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $userId = (int) $row['user_id'];
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
  <title>Reset Password - Printer Trooper Inc</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>

  <?php include 'includes/header.php'; ?>
  <main class="main-content">
    <div class="auth-container">
      <h2 style="text-align:center;color:#084298;">Reset Password</h2>

      <?php if (!empty($info)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($info); ?></div>
        <p class="hint"><a href="/printer-store/login.php">Back to login</a></p>
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
          <p class="hint"><a href="/printer-store/forgot_password.php">Request a new reset link</a></p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>