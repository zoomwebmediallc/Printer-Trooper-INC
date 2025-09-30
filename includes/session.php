<?php
session_start();

// --- Authentication helpers ---
function isLoggedIn() {
    return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
}

function getUserId() {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

function loginUser($user) {
    // $user can be an object with id/username/email or an associative array
    if (is_array($user)) {
        $_SESSION['user_id'] = (int)($user['id'] ?? 0);
        $_SESSION['username'] = $user['username'] ?? '';
        $_SESSION['email'] = $user['email'] ?? '';
        $_SESSION['first_name'] = $user['first_name'] ?? '';
        $_SESSION['last_name'] = $user['last_name'] ?? '';
    } else {
        $_SESSION['user_id'] = (int)($user->id ?? 0);
        $_SESSION['username'] = $user->username ?? '';
        $_SESSION['email'] = $user->email ?? '';
        $_SESSION['first_name'] = $user->first_name ?? '';
        $_SESSION['last_name'] = $user->last_name ?? '';
    }
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function getUserInfo($key) {
    return $_SESSION[$key] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?redirect=$redirect");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// --- Admin helpers ---
// Admins are a subset of users, gated by an allowlist defined in config/admin.php
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

function loginAdminIfAllowed() {
    // Called after a successful user login to elevate to admin if allowed
    $username = $_SESSION['username'] ?? '';
    $email = $_SESSION['email'] ?? '';
    // Lazy load config to avoid hard dependency for front pages
    $configPath = __DIR__ . '/../config/admin.php';
    if (is_file($configPath)) {
        include $configPath; // provides ADMIN_USERS array
        if (!empty($username) && !empty($ADMIN_USERS) && in_array($username, $ADMIN_USERS, true)) {
            $_SESSION['is_admin'] = true;
        } elseif (!empty($email) && !empty($ADMIN_USERS) && in_array($email, $ADMIN_USERS, true)) {
            $_SESSION['is_admin'] = true;
        }
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        // send to admin login
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/admin/orders.php');
        header("Location: /printer-store/admin/login.php?redirect=$redirect");
        exit();
    }
    if (!isAdmin()) {
        // try elevate if allowed (e.g., if user logged in from normal login)
        loginAdminIfAllowed();
        if (!isAdmin()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/admin/orders.php');
            header("Location: /printer-store/admin/login.php?redirect=$redirect");
            exit();
        }
    }
}
?>