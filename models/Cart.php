<?php
require_once './config/database.php';

class Cart {
    private $conn;
    private $table_name = "cart";

    public $id;
    public $user_id;
    public $product_id;
    public $quantity;
    public $added_at;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    private function ensureSchema() {
        // Add session_id column if not exists to support anonymous carts
        try {
            $this->conn->exec("ALTER TABLE `{$this->table_name}` ADD COLUMN `session_id` VARCHAR(128) NULL AFTER `user_id`");
        } catch (Exception $e) {
            // ignore if already exists
        }
        try {
            $this->conn->exec("CREATE INDEX idx_cart_session ON `{$this->table_name}` (`session_id`)");
        } catch (Exception $e) {
            // ignore if exists
        }
    }

    private function currentSessionId() {
        return session_id();
    }

    private function useUserContext() {
        return !empty($this->user_id);
    }

    // Add item to cart
    public function addToCart() {
        // Check if item already exists in cart for current context (user or session)
        if ($this->useUserContext()) {
            $query = "SELECT id, quantity FROM {$this->table_name} WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->user_id);
            $stmt->bindParam(2, $this->product_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "SELECT id, quantity FROM {$this->table_name} WHERE session_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $sid);
            $stmt->bindParam(2, $this->product_id);
        }
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            // Update quantity if item exists
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + $this->quantity;
            
            $update_query = "UPDATE {$this->table_name} SET quantity = ? WHERE id = ?";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(1, $new_quantity);
            $update_stmt->bindParam(2, $row['id']);
            
            return $update_stmt->execute();
        } else {
            // Insert new item
            if ($this->useUserContext()) {
                $insert_query = "INSERT INTO {$this->table_name} (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(":user_id", $this->user_id);
                $insert_stmt->bindParam(":product_id", $this->product_id);
                $insert_stmt->bindParam(":quantity", $this->quantity);
            } else {
                $sid = $this->currentSessionId();
                $insert_query = "INSERT INTO {$this->table_name} (session_id, product_id, quantity) VALUES (:session_id, :product_id, :quantity)";
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(":session_id", $sid);
                $insert_stmt->bindParam(":product_id", $this->product_id);
                $insert_stmt->bindParam(":quantity", $this->quantity);
            }
            
            return $insert_stmt->execute();
        }
    }

    // Get cart items for a user
    public function getCartItems($user_id) {
        if (!empty($user_id)) {
            $query = "SELECT c.id, c.quantity, c.added_at,
                             p.id as product_id, p.name, p.price, p.image_url, p.stock_quantity
                      FROM {$this->table_name} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.user_id = ?
                      ORDER BY c.added_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "SELECT c.id, c.quantity, c.added_at,
                             p.id as product_id, p.name, p.price, p.image_url, p.stock_quantity
                      FROM {$this->table_name} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.session_id = ?
                      ORDER BY c.added_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $sid);
        }
        $stmt->execute();
        return $stmt;
    }

    // Update cart item quantity
    public function updateQuantity($cart_id, $quantity) {
        // Ensure update only affects current owner (user or session)
        if ($this->useUserContext()) {
            $query = "UPDATE {$this->table_name} SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $quantity);
            $stmt->bindParam(2, $cart_id);
            $stmt->bindParam(3, $this->user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "UPDATE {$this->table_name} SET quantity = ? WHERE id = ? AND session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $quantity);
            $stmt->bindParam(2, $cart_id);
            $stmt->bindParam(3, $sid);
        }
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeFromCart($cart_id) {
        if ($this->useUserContext()) {
            $query = "DELETE FROM {$this->table_name} WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $cart_id);
            $stmt->bindParam(2, $this->user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "DELETE FROM {$this->table_name} WHERE id = ? AND session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $cart_id);
            $stmt->bindParam(2, $sid);
        }
        return $stmt->execute();
    }

    // Clear user's cart
    public function clearCart($user_id) {
        if (!empty($user_id)) {
            $query = "DELETE FROM {$this->table_name} WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "DELETE FROM {$this->table_name} WHERE session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $sid);
        }
        return $stmt->execute();
    }

    // Get cart total for a user
    public function getCartTotal($user_id) {
        if (!empty($user_id)) {
            $query = "SELECT SUM(c.quantity * p.price) as total
                      FROM {$this->table_name} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "SELECT SUM(c.quantity * p.price) as total
                      FROM {$this->table_name} c
                      JOIN products p ON c.product_id = p.id
                      WHERE c.session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $sid);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['total'] ? $row['total'] : 0;
    }

    // Get cart item count for a user
    public function getCartCount($user_id) {
        if (!empty($user_id)) {
            $query = "SELECT SUM(quantity) as count FROM {$this->table_name} WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
        } else {
            $sid = $this->currentSessionId();
            $query = "SELECT SUM(quantity) as count FROM {$this->table_name} WHERE session_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $sid);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['count'] ? $row['count'] : 0;
    }

    // Merge current session cart items into the given user's cart (used right after login)
    public function mergeSessionCartToUser($user_id) {
        if (empty($user_id)) return false;
        $sid = $this->currentSessionId();
        if (!$sid) return false;

        // Fetch all session cart items
        $getSessionItems = $this->conn->prepare("SELECT id, product_id, quantity FROM {$this->table_name} WHERE session_id = ?");
        $getSessionItems->execute([$sid]);
        $sessionItems = $getSessionItems->fetchAll(PDO::FETCH_ASSOC);

        if (!$sessionItems) return true;

        // For each session item, either merge quantity into existing user item or reassign to user
        $findUserItem = $this->conn->prepare("SELECT id, quantity FROM {$this->table_name} WHERE user_id = ? AND product_id = ? LIMIT 1");
        $updateQuantity = $this->conn->prepare("UPDATE {$this->table_name} SET quantity = ? WHERE id = ?");
        $reassignItem = $this->conn->prepare("UPDATE {$this->table_name} SET user_id = ?, session_id = NULL WHERE id = ?");

        foreach ($sessionItems as $item) {
            $findUserItem->execute([$user_id, $item['product_id']]);
            $existing = $findUserItem->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $newQty = (int)$existing['quantity'] + (int)$item['quantity'];
                // update existing user row
                $updateQuantity->execute([$newQty, $existing['id']]);
                // remove session row since merged
                $del = $this->conn->prepare("DELETE FROM {$this->table_name} WHERE id = ?");
                $del->execute([$item['id']]);
            } else {
                // reassign this session item to the user
                $reassignItem->execute([$user_id, $item['id']]);
            }
        }
        return true;
    }
}
?>