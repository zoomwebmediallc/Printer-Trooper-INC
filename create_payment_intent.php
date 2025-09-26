<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';
require_once __DIR__ . '/includes/promo.php';
require_once __DIR__ . '/includes/stripe_customer.php';

require_once __DIR__ . '/strip-intigration/stripe-php/init.php';
\Stripe\Stripe::setApiKey('sk_test_51PkpMI08O9gYqTfwypVHQeAV2Z7j1jhVpNgkDqcQRUKg8edIhUhUOhT6oeyZjIqfXZGHvAZ3dDvPPhkMT3KrYLfg00rtxdxrRz');

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
$cart_total = $cart->getCartTotal($userId);
$promo_code = trim($_POST['promo_code'] ?? '');
$promo = promo_calculate($db, $userId, $promo_code);
$discount = (float)($promo['discount'] ?? 0);
$pay_total = max(0.0, round($cart_total - $discount, 2));
$amount_cents = max(0, (int) round($pay_total * 100));

if ($amount_cents <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}
$shipping_address = trim($_POST['shipping_address'] ?? '');

try {
    // If logged in, attach to Stripe Customer to enable saved cards usage
    $customerId = null;
    if (!empty($userId)) {
        try { $customerId = ensure_user_stripe_customer_id($db, (int)$userId); } catch (Exception $e) { $customerId = null; }
    }

    $intent = \Stripe\PaymentIntent::create([
        'amount' => $amount_cents,
        'currency' => 'usd',
        'automatic_payment_methods' => ['enabled' => true],
        'customer' => $customerId ?: null,
        'setup_future_usage' => $customerId ? 'off_session' : null,
        'metadata' => [
            'user_id' => (string) ($userId ?? ''),
            'session_id' => session_id(),
            'shipping_address' => $shipping_address,
            'promo_code' => strtoupper($promo_code),
            'promo_discount' => (string)$discount,
            'cart_subtotal' => (string)$cart_total,
        ],
    ]);

    echo json_encode([
        'clientSecret' => $intent->client_secret,
        'amount' => $amount_cents,
        'promo' => [
            'code' => strtoupper($promo_code),
            'discount' => $discount,
            'message' => $promo['message'] ?? ''
        ]
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
