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
?>