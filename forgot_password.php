<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/PasswordReset.php';
require_once __DIR__ . '/includes/email_helper.php';

// If already logged in, just send them to homepage
if (isLoggedIn()) {
  header('Location: /printer-store/index.php');
  exit();
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$resetModel = new PasswordReset($db);

$info = '';
$error = '';

function site_base_url() {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  return $scheme . '://' . $host . $dir;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifier = trim($_POST['identifier'] ?? '');
  if ($identifier === '') {
    $error = 'Please provide your username or email.';
  } else {
    $user = $userModel->findByUsernameOrEmail($identifier);
    // Always generic response
    $info = 'If the account exists, we have sent a password reset link.';
    if ($user && isset($user['id'])) {
      try {
        $token = $resetModel->createToken((int)$user['id'], 30);
        $resetUrl = site_base_url() . '/reset_password.php?token=' . urlencode($token);
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
        $subject = $siteName . ' - Password Reset';
        $body = '<div style="font-family:Arial,sans-serif;line-height:1.6;">'
          . '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>'
          . '<p>We received a request to reset your password. Click the link below to set a new password. This link expires in 30 minutes.</p>'
          . '<p><a href="' . htmlspecialchars($resetUrl) . '">Reset your password</a></p>'
          . '<p>If you did not request this, you can ignore this email.</p>'
          . '<p>— ' . htmlspecialchars($siteName) . '</p>'
          . '</div>';
        @send_email_generic($user['email'], $subject, $body);
      } catch (Exception $e) {
        // ignore to avoid leaking
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password - Printer Trooper Inc</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <?php include 'includes/header.php'; ?>
  <main class="main-content">
    <div class="auth-container">
      <h2 style="text-align:center;margin-bottom:12px;color:#084298;">Forgot Password</h2>

      <?php if (!empty($info)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($info); ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="forgot_password.php">
        <div class="form-group">
          <label for="identifier">Username or Email</label>
          <input type="text" id="identifier" name="identifier" required />
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Send reset link</button>
        </div>
        <p class="hint"><a href="/printer-store/login.php">Back to login</a></p>
      </form>
    </div>
  </main>
</body>
</html>
