<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/stripe_customer.php';
require_once __DIR__ . '/strip-intigration/stripe-php/init.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Login required']); exit; }

$database = new Database();
$db = $database->getConnection();
$userId = getUserId();

try {
  $customerId = ensure_user_stripe_customer_id($db, $userId);
  $methods = \Stripe\PaymentMethod::all(['customer'=>$customerId, 'type'=>'card']);
  // Get default payment method from customer
  $cust = \Stripe\Customer::retrieve($customerId);
  $defaultId = null;
  if (isset($cust->invoice_settings) && isset($cust->invoice_settings->default_payment_method)) {
    $defaultId = $cust->invoice_settings->default_payment_method;
  }
  $list = [];
  foreach ($methods->data as $pm) {
    $list[] = [
      'id' => $pm->id,
      'brand' => $pm->card->brand,
      'last4' => $pm->card->last4,
      'exp_month' => $pm->card->exp_month,
      'exp_year' => $pm->card->exp_year,
      'is_default' => ($pm->id === $defaultId),
    ];
  }
  echo json_encode(['payment_methods'=>$list, 'default'=>$defaultId]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
