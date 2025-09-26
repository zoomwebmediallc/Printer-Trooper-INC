<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/promo.php';
require_once __DIR__ . '/models/Cart.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$userId = getUserId();
$subtotal = $cart->getCartTotal($userId);
$promo = trim($_POST['promo_code'] ?? '');
$res = promo_calculate($db, $userId, $promo);
$total_after = max(0.0, round($subtotal - ($res['discount'] ?? 0), 2));

echo json_encode([
  'discount' => (float)$res['discount'],
  'total_after' => $total_after,
  'message' => $res['message'] ?? '',
]);
