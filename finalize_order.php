<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/includes/email_helper.php';
require_once __DIR__ . '/config/email.php';
require_once __DIR__ . '/includes/promo.php';

header('Content-Type: application/json');

// Helper for absolute URL base (must be defined before use)
function get_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . '://' . $host . $dir;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$shipping_address = trim($_POST['shipping_address'] ?? '');
$customer_email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
$stripe_pi = trim($_POST['payment_intent_id'] ?? '');
$promo_code = trim($_POST['promo_code'] ?? '');

if ($shipping_address === '' || $customer_email === '' || $stripe_pi === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $cart = new Cart($db);

    $userId = getUserId();
    $cart_total = $cart->getCartTotal($userId);
    $promo = promo_calculate($db, $userId, $promo_code);
    $discount = (float)($promo['discount'] ?? 0);
    $pay_total = max(0.0, round($cart_total - $discount, 2));
    if ($cart_total <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart is empty']);
        exit;
    }

    $cart_items_stmt = $cart->getCartItems(getUserId());

    $orderModel = new Order($db);
    $sessionId = session_id();

    $order = $orderModel->createOrder(
        $sessionId,
        $customer_email,
        $shipping_address,
        'stripe',
        $stripe_pi,
        $cart_items_stmt,
        $pay_total,
        $userId
    );

    // Clear cart after order creation
    $cart->clearCart($userId);

    // Prepare email content
    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
    $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';

    $itemsHtml = '';
    foreach ($order['items'] as $it) {
        $lineTotal = number_format($it['price'] * $it['quantity'], 2);
        $itemsHtml .= '<tr>' .
            '<td style="padding:8px;border:1px solid #eee;">' . htmlspecialchars($it['name'] ?? ('#' . $it['product_id'])) . '</td>' .
            '<td style="padding:8px;border:1px solid #eee;text-align:center;">' . (int)$it['quantity'] . '</td>' .
            '<td style="padding:8px;border:1px solid #eee;text-align:right;">$' . number_format($it['price'], 2) . '</td>' .
            '<td style="padding:8px;border:1px solid #eee;text-align:right;">$' . $lineTotal . '</td>' .
            '</tr>';
    }

    $orderSummaryTable = '<table style="border-collapse:collapse;width:100%;">' .
        '<thead><tr>' .
        '<th style="padding:8px;border:1px solid #eee;text-align:left;">Product</th>' .
        '<th style="padding:8px;border:1px solid #eee;">Qty</th>' .
        '<th style="padding:8px;border:1px solid #eee;text-align:right;">Price</th>' .
        '<th style="padding:8px;border:1px solid #eee;text-align:right;">Total</th>' .
        '</tr></thead>' .
        '<tbody>' . $itemsHtml . '</tbody>' .
        '<tfoot>' .
        '<tr><td colspan="3" style="padding:8px;border:1px solid #eee;text-align:right;">Subtotal</td><td style="padding:8px;border:1px solid #eee;text-align:right;">$' . number_format($cart_total, 2) . '</td></tr>' .
        ($discount > 0 ? '<tr><td colspan="3" style="padding:8px;border:1px solid #eee;text-align:right;">Discount (' . htmlspecialchars(strtoupper($promo_code)) . ')</td><td style="padding:8px;border:1px solid #eee;text-align:right;">-$' . number_format($discount, 2) . '</td></tr>' : '') .
        '<tr><td colspan="3" style="padding:8px;border:1px solid #eee;text-align:right;"><strong>Grand Total</strong></td><td style="padding:8px;border:1px solid #eee;text-align:right;"><strong>$' . number_format($pay_total, 2) . '</strong></td></tr>' .
        '</tfoot>' .
        '</table>';

    // Build PDF receipt (best-effort)
    $pdfBytes = '';
    try {
        require_once __DIR__ . '/includes/lib/fpdf.php';
        if (class_exists('FPDF')) {
            $pdf = new FPDF('P','mm','A4');
            $pdf->AddPage();
            $pdf->SetFont('helvetica','',16);
            $pdf->Cell(190,10,$siteName,0,1,'L');
            $pdf->SetFont('helvetica','',12);
            $pdf->Cell(190,8,'Order Receipt',0,1,'L');
            $pdf->Cell(190,6,'Order #: ' . $order['order_number'],0,1,'L');
            $pdf->Cell(190,6,'Date: ' . ($order['order_date'] ?? date('Y-m-d H:i:s')),0,1,'L');
            $pdf->Ln(2);
            $pdf->Cell(190,6,'Shipping Address:',0,1,'L');
            $pdf->MultiCell(190,6,(string)$order['shipping_address']);
            $pdf->Ln(2);
            $pdf->Cell(100,8,'Item',0,0,'L');
            $pdf->Cell(30,8,'Qty',0,0,'L');
            $pdf->Cell(30,8,'Price',0,0,'R');
            $pdf->Cell(30,8,'Total',0,1,'R');
            foreach ($order['items'] as $it) {
                $line = number_format($it['price'] * $it['quantity'], 2);
                $pdf->Cell(100,6, (string)($it['name'] ?? ('#'.$it['product_id'])),0,0,'L');
                $pdf->Cell(30,6, (string)((int)$it['quantity']),0,0,'L');
                $pdf->Cell(30,6, '$'.number_format($it['price'],2),0,0,'R');
                $pdf->Cell(30,6, '$'.$line,0,1,'R');
            }
            $pdf->Ln(2);
            $pdf->Cell(160,6,'Subtotal',0,0,'R');
            $pdf->Cell(30,6,'$'.number_format($cart_total,2),0,1,'R');
            if ($discount > 0) {
                $pdf->Cell(160,6,'Discount ('.strtoupper($promo_code).')',0,0,'R');
                $pdf->Cell(30,6,'-$'.number_format($discount,2),0,1,'R');
            }
            $pdf->SetFont('helvetica','',13);
            $pdf->Cell(160,8,'Grand Total',0,0,'R');
            $pdf->Cell(30,8,'$'.number_format($pay_total,2),0,1,'R');
            if (method_exists($pdf, 'Output')) {
                $pdfBytes = $pdf->Output('S','receipt.pdf');
            }
        }
    } catch (Exception $e) { /* ignore PDF errors */ }

    // Customer email
    $customerSubject = "Your order {$order['order_number']} is confirmed";
    $customerBody = '<div style="font-family:Arial,sans-serif;line-height:1.6;">' .
        '<h2 style="color:#27ae60;">Thank you for your purchase!</h2>' .
        '<p>Your order has been placed successfully. Keep this order number for tracking:</p>' .
        '<p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>' .
        '<p><strong>Shipping Address:</strong><br>' . nl2br(htmlspecialchars($order['shipping_address'])) . '</p>' .
        $orderSummaryTable .
        '<p>You can track your order anytime here: <a href="' . htmlspecialchars(get_base_url() . '/track_order.php') . '">Track Order</a></p>' .
        ($discount > 0 ? '<p><strong>Discount applied:</strong> ' . htmlspecialchars(strtoupper($promo_code)) . ' (-$' . number_format($discount,2) . ')</p>' : '') .
        '<p>— ' . htmlspecialchars($siteName) . '</p>' .
        '</div>';

    // Admin email
    $adminSubject = "New order received: {$order['order_number']}";
    $adminBody = '<div style="font-family:Arial,sans-serif;line-height:1.6;">' .
        '<h2>New Order</h2>' .
        '<p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>' .
        '<p><strong>Email:</strong> ' . htmlspecialchars($order['customer_email']) . '</p>' .
        '<p><strong>Total:</strong> $' . number_format($order['total_amount'], 2) . '</p>' .
        '<p><strong>Shipping Address:</strong><br>' . nl2br(htmlspecialchars($order['shipping_address'])) . '</p>' .
        $orderSummaryTable .
        '</div>';

    // Send emails (best-effort). Attach PDF to both customer and admin emails if possible.
    @send_email_with_attachment($customer_email, $customerSubject, $customerBody, 'receipt-'.$order['order_number'].'.pdf', $pdfBytes, 'application/pdf');
    @send_email_with_attachment($adminEmail, $adminSubject, $adminBody, 'receipt-'.$order['order_number'].'.pdf', $pdfBytes, 'application/pdf');

    echo json_encode([
        'success' => true,
        'order_number' => $order['order_number'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    $msg = 'Failed to finalize order';
    if ($e && $e->getMessage()) {
        $msg .= ': ' . $e->getMessage();
    }
    echo json_encode(['error' => $msg]);
}
