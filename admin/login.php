<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

// If already an admin, bounce to orders page or requested target
if (isLoggedIn()) {
  loginAdminIfAllowed();
  if (isAdmin()) {
    $target = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/printer-store/admin/orders.php';
    if (strpos($target, '/printer-store/') === 0) {
      header("Location: $target");
    } else {
      header('Location: /printer-store/admin/orders.php');
    }
    exit();
  }
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = '';

function safe_redirect_target_admin($val)
{
  $raw = trim((string) $val);
  if ($raw === '')
    return '';
  $raw = urldecode($raw);
  if (strpos($raw, '/printer-store/') !== 0)
    return '';
  return $raw;
}

$incomingRedirect = $_GET['redirect'] ?? '';
$redirectTarget = safe_redirect_target_admin($incomingRedirect);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $redirect = safe_redirect_target_admin($_POST['redirect'] ?? '');

  if ($username === '' || $password === '') {
    $error = 'Please fill in all fields.';
  } else {
    if ($user->login($username, $password)) {
      // Persist session and attempt to elevate
      loginUser($user);
      loginAdminIfAllowed();
      if (!isAdmin()) {
        // Not in allowlist
        $error = 'You do not have admin access.';
        // Ensure any prior admin flag is removed
        $_SESSION['is_admin'] = false;
      } else {
        $target = $redirect !== '' ? $redirect : '/printer-store/admin/orders.php';
        header("Location: $target");
        exit();
      }
    } else {
      $error = 'Invalid username/email or password.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login - Printer Trooper Inc</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style>
    .auth-container {
      max-width: 420px;
      margin: 60px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .alert {
      padding: 10px 12px;
      border-radius: 6px;
      margin-bottom: 12px;
    }

    .alert-error {
      background: #fde2e2;
      color: #7d1d1d;
    }

    .form-group {
      margin-bottom: 12px;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form-actions {
      margin-top: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-primary {
      background: #084298;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    .hint {
      font-size: 13px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>

<body>
  <main class="main-content">
    <div class="auth-container">
      <h2 style="text-align:center; color:#084298;">Admin Login</h2>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label for="username">Username or Email</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>"
            required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <?php if (!empty($redirectTarget)): ?>
          <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>" />
        <?php endif; ?>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Login</button>
        </div>
        <a href="/printer-store/index.php" class="hint">Back to site</a>
        <p class="hint">Only allowlisted accounts can access the admin dashboard.</p>
      </form>
    </div>
  </main>
</body>

</html>