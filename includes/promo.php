<?php
require_once __DIR__ . '/../config/database.php';

function promo_calculate(PDO $db, ?int $userId, string $promoCode): array {
    $promoCode = strtoupper(trim($promoCode));
    if ($promoCode === '') return ['discount' => 0.0, 'code' => '', 'message' => ''];

    // Get cart subtotal
    $cartSubtotal = 0.0;
    if (!empty($userId)) {
        $sql = "SELECT SUM(c.quantity * p.price) AS subtotal FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=:uid";
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
    } else {
        $sid = session_id();
        $sql = "SELECT SUM(c.quantity * p.price) AS subtotal FROM cart c JOIN products p ON c.product_id=p.id WHERE c.session_id=:sid";
        $stmt = $db->prepare($sql);
        $stmt->execute([':sid' => $sid]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $cartSubtotal = (float)($row['subtotal'] ?? 0);

    $discount = 0.0;
    $message = '';

    if ($promoCode === 'FIRST20') {
        // First purchase only (user must exist and have zero orders)
        if (empty($userId)) {
            $message = 'Login required to use FIRST20.';
        } else {
            $cntStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE user_id=:uid");
            $cntStmt->execute([':uid' => $userId]);
            $cnt = (int)($cntStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
            if ($cnt === 0) {
                $discount = round($cartSubtotal * 0.20, 2);
                $message = '20% welcome discount applied.';
            } else {
                $message = 'FIRST20 is only for your first order.';
            }
        }
    } elseif ($promoCode === 'ACCESS10') {
        // 10% off accessories category subtotal
        // Try to find category id by name 'Accessories'
        $catId = null;
        try {
            $c = $db->query("SELECT id FROM categories WHERE LOWER(name)='accessories' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($c) $catId = (int)$c['id'];
        } catch (Exception $e) {}

        if ($catId) {
            if (!empty($userId)) {
                $sqlA = "SELECT SUM(c.quantity * p.price) AS suba FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=:uid AND p.category_id=:cid";
                $stA = $db->prepare($sqlA);
                $stA->execute([':uid' => $userId, ':cid' => $catId]);
            } else {
                $sid = session_id();
                $sqlA = "SELECT SUM(c.quantity * p.price) AS suba FROM cart c JOIN products p ON c.product_id=p.id WHERE c.session_id=:sid AND p.category_id=:cid";
                $stA = $db->prepare($sqlA);
                $stA->execute([':sid' => $sid, ':cid' => $catId]);
            }
            $subA = (float)($stA->fetch(PDO::FETCH_ASSOC)['suba'] ?? 0);
            $discount = round($subA * 0.10, 2);
            if ($discount > 0) {
                $message = '10% off accessories applied.';
            } else {
                $message = 'No accessories found in cart for ACCESS10.';
            }
        } else {
            $message = 'Accessories category not found.';
        }
    }

    $discount = max(0.0, min($discount, $cartSubtotal));
    return ['discount' => $discount, 'code' => $promoCode, 'message' => $message];
}
