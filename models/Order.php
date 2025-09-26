<?php
require_once __DIR__ . '/../config/database.php';

class Order
{
    private $conn;
    private $orders_table = 'orders';
    private $order_items_table = 'order_items';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureSchema();
    }

    private function ensureSchema()
    {
        // Add additional columns needed for guest checkout + tracking
        try {
            $this->conn->exec("ALTER TABLE `{$this->orders_table}` ADD COLUMN `order_number` VARCHAR(32) UNIQUE AFTER `id`");
        } catch (Exception $e) {
            // ignore if exists
        }
        try {
            $this->conn->exec("ALTER TABLE `{$this->orders_table}` ADD COLUMN `customer_email` VARCHAR(255) NULL AFTER `user_id`");
        } catch (Exception $e) {
            // ignore if exists
        }
        try {
            $this->conn->exec("ALTER TABLE `{$this->orders_table}` ADD COLUMN `stripe_payment_intent_id` VARCHAR(64) NULL AFTER `payment_method`");
        } catch (Exception $e) {
            // ignore if exists
        }
        try {
            $this->conn->exec("ALTER TABLE `{$this->orders_table}` ADD COLUMN `session_id` VARCHAR(128) NULL AFTER `user_id`");
        } catch (Exception $e) {
            // ignore if exists
        }
        try {
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_orders_email ON `{$this->orders_table}` (`customer_email`)");
        } catch (Exception $e) {
            // some MySQL versions don't support IF NOT EXISTS for index creation, try without and ignore errors
            try {
                $this->conn->exec("CREATE INDEX idx_orders_email ON `{$this->orders_table}` (`customer_email`)");
            } catch (Exception $ignored) {}
        }
    }

    private function generateOrderNumber(): string
    {
        // Simple unique order number: PS-YYYYMMDD-XXXXXX
        $prefix = 'PS-' . date('Ymd') . '-';
        $random = strtoupper(substr(bin2hex(random_bytes(6)), 0, 6));
        return $prefix . $random;
    }

    public function createOrder($sessionId, $customerEmail, $shippingAddress, $paymentMethod, $stripePaymentIntentId, $cartItemsStmt, $totalAmount, $userId = null)
    {
        $this->conn->beginTransaction();
        try {
            $orderNumber = $this->generateOrderNumber();

            $insertOrderSql = "INSERT INTO {$this->orders_table} (order_number, user_id, session_id, customer_email, total_amount, status, shipping_address, payment_method, stripe_payment_intent_id, order_date)
                               VALUES (:order_number, :user_id, :session_id, :customer_email, :total_amount, 'processing', :shipping_address, :payment_method, :pi, NOW())";
            $stmt = $this->conn->prepare($insertOrderSql);
            $stmt->bindValue(':order_number', $orderNumber);
            // Store user_id if provided, otherwise NULL
            if (!empty($userId)) {
                $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':session_id', $sessionId);
            $stmt->bindValue(':customer_email', $customerEmail);
            $stmt->bindValue(':total_amount', $totalAmount);
            $stmt->bindValue(':shipping_address', $shippingAddress);
            $stmt->bindValue(':payment_method', $paymentMethod);
            $stmt->bindValue(':pi', $stripePaymentIntentId);
            $stmt->execute();
            $orderId = (int)$this->conn->lastInsertId();

            // Insert order items
            $insertItemSql = "INSERT INTO {$this->order_items_table} (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $insertItemStmt = $this->conn->prepare($insertItemSql);

            // Reduce stock for each product
            $updateStockSql = "UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - :qty) WHERE id = :pid";
            $updateStockStmt = $this->conn->prepare($updateStockSql);

            $items = [];
            while ($item = $cartItemsStmt->fetch(PDO::FETCH_ASSOC)) {
                $insertItemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                ]);
                $updateStockStmt->execute([
                    ':qty' => $item['quantity'],
                    ':pid' => $item['product_id'],
                ]);
                $items[] = $item;
            }

            $this->conn->commit();
            return [
                'id' => $orderId,
                'order_number' => $orderNumber,
                'customer_email' => $customerEmail,
                'total_amount' => $totalAmount,
                'status' => 'processing',
                'shipping_address' => $shippingAddress,
                'payment_method' => $paymentMethod,
                'stripe_payment_intent_id' => $stripePaymentIntentId,
                'items' => $items,
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function findByOrderNumberAndEmail($orderNumber, $email)
    {
        $sql = "SELECT * FROM {$this->orders_table} WHERE order_number = :onum AND customer_email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':onum' => $orderNumber, ':email' => $email]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;

        $itemsSql = "SELECT oi.*, p.name, p.image_url FROM {$this->order_items_table} oi JOIN products p ON oi.product_id = p.id WHERE order_id = :oid";
        $itemsStmt = $this->conn->prepare($itemsSql);
        $itemsStmt->execute([':oid' => $order['id']]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        return $order;
    }

    public function getValidStatuses(): array
    {
        return ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    }

    public function listOrders($status = null, $limit = 50, $offset = 0)
    {
        $params = [];
        $sql = "SELECT * FROM {$this->orders_table}";
        if (!empty($status)) {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY order_date DESC, id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($orderId, $newStatus)
    {
        $valid = $this->getValidStatuses();
        if (!in_array($newStatus, $valid, true)) {
            throw new InvalidArgumentException('Invalid status');
        }
        $sql = "UPDATE {$this->orders_table} SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $newStatus, ':id' => $orderId]);
    }

    public function findById($orderId)
    {
        $sql = "SELECT * FROM {$this->orders_table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;
        $itemsSql = "SELECT oi.*, p.name FROM {$this->order_items_table} oi JOIN products p ON oi.product_id = p.id WHERE order_id = :oid";
        $itemsStmt = $this->conn->prepare($itemsSql);
        $itemsStmt->execute([':oid' => $order['id']]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        return $order;
    }
}
