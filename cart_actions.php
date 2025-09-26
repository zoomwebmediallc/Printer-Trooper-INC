<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'models/Cart.php';

// No login required for cart actions
$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);
$cart->user_id = getUserId();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'add':
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;

        if ($product_id && $quantity > 0) {
            $cart->product_id = $product_id;
            $cart->quantity = $quantity;

            if ($cart->addToCart()) {
                $response['success'] = true;
                $response['message'] = 'Product added to cart successfully!';
                header("Location: cart.php?success=added");
                exit();
            } else {
                $response['message'] = 'Failed to add product to cart.';
            }
        } else {
            $response['message'] = 'Invalid product or quantity.';
        }
        break;

    case 'update':
        $cart_id = $_POST['cart_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;

        if ($cart_id && $quantity > 0) {
            if ($cart->updateQuantity($cart_id, $quantity)) {
                $response['success'] = true;
                $response['message'] = 'Cart updated successfully!';

                header("Location: cart.php?success=updated");
                exit();
            } else {
                $response['message'] = 'Failed to update cart.';
            }
        } else {
            $response['message'] = 'Invalid cart item or quantity.';
        }
        break;

    case 'remove':
        $cart_id = $_POST['cart_id'] ?? 0;

        if ($cart_id) {
            if ($cart->removeFromCart($cart_id)) {
                $response['success'] = true;
                $response['message'] = 'Item removed from cart successfully!';

                header("Location: cart.php?success=removed");
                exit();
            } else {
                $response['message'] = 'Failed to remove item from cart.';
            }
        } else {
            $response['message'] = 'Invalid cart item.';
        }
        break;

    case 'clear':
        if ($cart->clearCart(getUserId())) {
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully!';

            header("Location: cart.php?success=cleared");
            exit();
        } else {
            $response['message'] = 'Failed to clear cart.';
        }
        break;

    default:
        $response['message'] = 'Invalid action.';
        break;
}

header("Location: cart.php?error=" . urlencode($response['message']));
exit();
