<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/stripe_customer.php';
require_once __DIR__ . '/strip-intigration/stripe-php/init.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Login required']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

$pmId = trim($_POST['payment_method_id'] ?? '');
if ($pmId === '') { http_response_code(400); echo json_encode(['error'=>'Missing payment_method_id']); exit; }

$database = new Database();
$db = $database->getConnection();
$userId = getUserId();

try {
  $customerId = ensure_user_stripe_customer_id($db, $userId);
  // Detach payment method from customer
  $pm = \Stripe\PaymentMethod::retrieve($pmId);
  if ($pm->customer !== $customerId) {
    http_response_code(403);
    echo json_encode(['error'=>'Not allowed']);
    exit;
  }
  $pm->detach();
  echo json_encode(['success'=>true]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
