<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../strip-intigration/stripe-php/init.php';

\Stripe\Stripe::setApiKey('sk_test_51PkpMI08O9gYqTfwypVHQeAV2Z7j1jhVpNgkDqcQRUKg8edIhUhUOhT6oeyZjIqfXZGHvAZ3dDvPPhkMT3KrYLfg00rtxdxrRz');

function ensure_user_stripe_customer_id(PDO $db, int $userId): string {
    // Ensure column exists
    try { $db->exec("ALTER TABLE users ADD COLUMN stripe_customer_id VARCHAR(64) NULL"); } catch (Exception $e) {}

    // Load user
    $stmt = $db->prepare('SELECT id, email, first_name, last_name, stripe_customer_id FROM users WHERE id=:id LIMIT 1');
    $stmt->execute([':id'=>$userId]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) throw new Exception('User not found');

    if (!empty($u['stripe_customer_id'])) {
        return $u['stripe_customer_id'];
    }

    // Create customer in Stripe
    $customer = \Stripe\Customer::create([
        'email' => $u['email'] ?? null,
        'name' => trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: null,
        'metadata' => [ 'user_id' => (string)$userId ],
    ]);

    $cid = $customer->id;
    $upd = $db->prepare('UPDATE users SET stripe_customer_id=:cid WHERE id=:id');
    $upd->execute([':cid'=>$cid, ':id'=>$userId]);
    return $cid;
}
