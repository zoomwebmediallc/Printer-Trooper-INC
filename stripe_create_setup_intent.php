<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/stripe_customer.php';
require_once __DIR__ . '/strip-intigration/stripe-php/init.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Login required']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

$database = new Database();
$db = $database->getConnection();
$userId = getUserId();

try {
  $customerId = ensure_user_stripe_customer_id($db, $userId);
  $intent = \Stripe\SetupIntent::create([
    'customer' => $customerId,
    'payment_method_types' => ['card'],
  ]);
  echo json_encode(['clientSecret' => $intent->client_secret]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
