<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';
requireLogin();
$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);

$cart_items = $cart->getCartItems(getUserId());
$cart_total = $cart->getCartTotal(getUserId());
$cart_count = $cart->getCartCount(getUserId());

if ($cart_count == 0 && !(isset($_GET['success']) && $_GET['success'] == '1')) {
    header("Location: cart.php");
    exit();
}

$success = isset($_GET['success']);
$orderNo = isset($_GET['order']) ? preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['order']) : '';
$error = '';
$stripePublishableKey = 'pk_test_51PkpMI08O9gYqTfwYHnxfcYBY2raZGkRqgZSy0QyOy9tbTSMuJjBaE10uaGe0W5DLRxCAVW7elsfp15iQeETKmkZ00OdpLrGxn';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Trooper Inc - Checkout</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- <link rel="icon" type="image/png" href="./assets/images/favicon.png"> -->
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <?php if ($success): ?>
                <div class="auth-container" style="max-width: 600px; text-align: center;">
                    <h2 style="color: #27ae60; margin-bottom: 1rem;">Order Placed Successfully!</h2>
                    <div class="alert alert-success">
                        <p>Thank you for your order! Your order has been placed successfully.</p>
                        <p>You will receive a confirmation email shortly.</p>
                        <?php if (!empty($orderNo)): ?>
                            <p>Your Order Number: <strong><?php echo htmlspecialchars($orderNo); ?></strong></p>
                            <p style="margin-top: 1rem;"><a class="btn-primary"
                                    href="track_order.php?order=<?php echo urlencode($orderNo); ?>">Track Your Order</a></p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="index.php" class="btn-primary">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <h1 class="page-title">Checkout</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
                    <div>
                        <div
                            style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                            <h3 style="margin-bottom: 1rem; color: #2c3e50;">Order Summary</h3>

                            <?php while ($item = $cart_items->fetch(PDO::FETCH_ASSOC)): ?>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
                                    <div>
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p style="color: #6c757d; margin: 0;">Quantity: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div>
                                        <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endwhile; ?>

                            <div style="padding: 1rem 0; font-size: 1.2rem;">
                                <strong>Total: $<?php echo number_format($cart_total, 2); ?></strong>
                            </div>
                        </div>

                        <form method="POST" action="checkout.php" id="payment-form"
                            style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">Shipping & Payment</h3>

                            <div class="form-group">
                                <label for="email">Email Address: *</label>
                                <input type="email" id="email" name="email" required
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                            </div>

                            <div class="form-group">
                                <label for="shipping_address">Shipping Address: *</label>
                                <textarea id="shipping_address" name="shipping_address"
                                    required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="card-element">Card Details</label>
                                <div id="card-element"
                                    style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: #fafafa;">
                                </div>
                                <div id="card-errors" role="alert" style="color: #e74c3c; margin-top: 8px;"></div>
                            </div>

                            <div class="form-group">
                                <label for="promo_code">Voucher code</label>
                                <div style="display:flex; gap:.5rem;">
                                    <input type="text" id="promo_code" name="promo_code" placeholder="Enter code (e.g., FIRST20)" style="flex:1;" value="<?php echo htmlspecialchars($_POST['promo_code'] ?? ''); ?>" />
                                    <button type="button" class="btn-primary" id="applyPromoBtn">Apply</button>
                                </div>
                                <small id="promoMsg" style="color:#6c757d;"></small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary" id="stripePayBtn">Pay with Card (Stripe)</button>
                            </div>

                            <p style="font-size: 0.9rem; color: #6c757d; text-align: center; margin-top: 1rem;">
                                By placing this order, you agree to our terms and conditions.
                            </p>
                        </form>
                    </div>

                    <!-- Order Info -->
                    <div>
                        <div
                            style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <h3 style="margin-bottom: 1rem; color: #2c3e50;">Order Information</h3>

                            <!-- <div style="margin-bottom: 1rem;">
                                <strong>Customer:</strong><br>
                                <?php echo htmlspecialchars(getUserInfo('first_name') . ' ' . getUserInfo('last_name')); ?><br>
                                <?php echo htmlspecialchars(getUserInfo('email')); ?>
                            </div> -->

                            <div style="margin-bottom: 1rem;">
                                <strong>Items:</strong> <?php echo $cart_count; ?><br>
                                <strong>Subtotal:</strong> $<?php echo number_format($cart_total, 2); ?><br>
                                <strong>Shipping:</strong> FREE<br>
                                <strong>Total:</strong> $<?php echo number_format($cart_total, 2); ?>
                            </div>

                            <div
                                style="padding: 1rem; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem; color: #6c757d;">
                                <strong>Free Shipping</strong><br>
                                All orders ship free of charge!
                            </div>
                            <div id="promoSummary" style="margin-top:10px;color:#2c3e50;font-weight:600;display:none;"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (function () {
            const stripe = Stripe('<?php echo $stripePublishableKey; ?>');
            const elements = stripe.elements();
            const card = elements.create('card', {
                hidePostalCode: true
            });
            card.mount('#card-element');

            const form = document.getElementById('payment-form');
            const payBtn = document.getElementById('stripePayBtn');
            const cardErrors = document.getElementById('card-errors');
            const promoInput = document.getElementById('promo_code');
            const promoMsg = document.getElementById('promoMsg');
            const promoSummary = document.getElementById('promoSummary');

            async function previewPromo() {
                const code = (promoInput.value || '').trim();
                if (!code) { promoMsg.textContent = 'Enter a voucher like FIRST20 or ACCESS10'; return; }
                const body = new URLSearchParams();
                body.append('promo_code', code);
                try {
                    const res = await fetch('promo_preview.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.error || 'Failed to preview');
                    promoMsg.textContent = json.message || '';
                    if (json.discount && json.total_after) {
                        promoSummary.style.display = 'block';
                        promoSummary.textContent = `Voucher applied: -$${Number(json.discount).toFixed(2)} · New total: $${Number(json.total_after).toFixed(2)}`;
                    } else {
                        promoSummary.style.display = 'none';
                        promoSummary.textContent = '';
                    }
                } catch (e) {
                    promoMsg.textContent = e.message;
                    promoSummary.style.display = 'none';
                }
            }

            const applyBtn = document.getElementById('applyPromoBtn');
            applyBtn && applyBtn.addEventListener('click', (e)=>{ e.preventDefault(); previewPromo(); });

            function setLoading(loading) {
                if (loading) {
                    payBtn.setAttribute('disabled', 'disabled');
                    payBtn.textContent = 'Processing...';
                } else {
                    payBtn.removeAttribute('disabled');
                    payBtn.textContent = 'Pay with Card (Stripe)';
                }
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                cardErrors.textContent = '';
                const shippingAddress = document.getElementById('shipping_address').value.trim();
                const email = document.getElementById('email').value.trim();
                if (!shippingAddress) {
                    cardErrors.textContent = 'Please enter a shipping address.';
                    return;
                }
                if (!email) {
                    cardErrors.textContent = 'Please enter a valid email address.';
                    return;
                }

                setLoading(true);
                try {
                    const body = new URLSearchParams();
                    body.append('shipping_address', shippingAddress);
                    if (promoInput && promoInput.value) body.append('promo_code', promoInput.value.trim());

                    const res = await fetch('create_payment_intent.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body
                    });
                    // Robust JSON parsing with fallback to text (handles HTML/PHP error output)
                    const rawCreate = await res.text();
                    let data;
                    try {
                        data = rawCreate ? JSON.parse(rawCreate) : {};
                    } catch (e) {
                        throw new Error('Payment initialization failed. ' + rawCreate.replace(/<[^>]+>/g, '').trim());
                    }
                    if (!res.ok) {
                        throw new Error((data && data.error) ? data.error : 'Failed to create payment');
                    }

                    const clientSecret = data.clientSecret;

                    const {
                        error,
                        paymentIntent
                    } = await stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: card,
                        }
                    });

                    if (error) {
                        throw new Error(error.message);
                    }

                    if (paymentIntent && paymentIntent.status === 'succeeded') {
                        // Finalize order on server (create order, send emails, clear cart)
                        const finalizeBody = new URLSearchParams();
                        finalizeBody.append('shipping_address', shippingAddress);
                        finalizeBody.append('email', email);
                        finalizeBody.append('payment_intent_id', paymentIntent.id);
                        if (promoInput && promoInput.value) finalizeBody.append('promo_code', promoInput.value.trim());

                        const finalizeRes = await fetch('finalize_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: finalizeBody
                        });
                        const rawFinalize = await finalizeRes.text();
                        let finalizeData;
                        try {
                            finalizeData = rawFinalize ? JSON.parse(rawFinalize) : {};
                        } catch (e) {
                            throw new Error('Order finalization failed. ' + rawFinalize.replace(/<[^>]+>/g, '').trim());
                        }
                        if (!finalizeRes.ok || !finalizeData.success) {
                            throw new Error((finalizeData && finalizeData.error) ? finalizeData.error : 'Failed to finalize order');
                        }
                        const orderNo = finalizeData.order_number;
                        window.location.href = 'checkout.php?success=1&order=' + encodeURIComponent(orderNo);
                        return;
                    } else {
                        throw new Error('Payment not completed.');
                    }
                } catch (err) {
                    cardErrors.textContent = err.message || 'Payment failed';
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
    <script src="./assets/js/script.js"></script>
</body>

</html>