<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../includes/email_helper.php';

// If already admin, go to orders
if (isLoggedIn()) {
  loginAdminIfAllowed();
  if (isAdmin()) {
    header('Location: /printer-store/admin/orders.php');
    exit();
  }
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$resetModel = new PasswordReset($db);

$info = '';
$error = '';

function admin_base_url() {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  // Ensure we return base path without trailing /admin
  return $scheme . '://' . $host . preg_replace('#/admin$#','',$dir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifier = trim($_POST['identifier'] ?? '');
  if ($identifier === '') {
    $error = 'Please provide your username or email.';
  } else {
    $user = $userModel->findByUsernameOrEmail($identifier);
    // Always show generic message to avoid user enumeration
    $info = 'If the account exists, we have sent a password reset link.';
    if ($user && isset($user['id'])) {
      try {
        $token = $resetModel->createToken((int)$user['id'], 30);
        $resetUrl = admin_base_url() . '/admin/reset_password.php?token=' . urlencode($token);
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
        $subject = $siteName . ' - Password Reset';
        $body = '<div style="font-family:Arial,sans-serif;line-height:1.6;">'
          . '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>'
          . '<p>We received a request to reset your password. Click the link below to set a new password. This link expires in 30 minutes.</p>'
          . '<p><a href="' . htmlspecialchars($resetUrl) . '">Reset your password</a></p>'
          . '<p>If you did not request this, you can ignore this email.</p>'
          . '<p>— ' . htmlspecialchars($siteName) . '</p>'
          . '</div>';
        // send email
        @send_email_generic($user['email'], $subject, $body);
      } catch (Exception $e) {
        // swallow to avoid leaking state
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
  <title>Admin - Forgot Password</title>
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
      <h2 style="text-align:center;color:#084298;">Admin - Forgot Password</h2>

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
        <p class="hint"><a href="/printer-store/admin/login.php">Back to login</a></p>
      </form>
    </div>
  </main>
</body>
</html>
