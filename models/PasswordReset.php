<?php
class PasswordReset {
    private $conn;
    private $table = 'password_resets';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a reset token for a user and return the plain token (not hashed)
    public function createToken($userId, $ttlMinutes = 30) {
        $token = bin2hex(random_bytes(32)); // 64 hex chars
        $hash = hash('sha256', $token);
        $expiresAt = (new DateTime('+'.$ttlMinutes.' minutes'))->format('Y-m-d H:i:s');
        $sql = "INSERT INTO {$this->table} (user_id, token_hash, expires_at) VALUES (:uid, :th, :exp)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':th', $hash);
        $stmt->bindParam(':exp', $expiresAt);
        $stmt->execute();
        return $token;
    }

    // Verify token: returns assoc array with row or false
    public function verifyToken($token) {
        if (empty($token)) return false;
        $hash = hash('sha256', $token);
        $sql = "SELECT * FROM {$this->table} WHERE token_hash = :th LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':th', $hash);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        if (!empty($row['used_at'])) return false;
        if (strtotime($row['expires_at']) < time()) return false;
        return $row;
    }

    // Consume token: mark used and return true/false
    public function consumeToken($token) {
        $hash = hash('sha256', $token);
        $usedAt = (new DateTime())->format('Y-m-d H:i:s');
        $sql = "UPDATE {$this->table} SET used_at = :ua WHERE token_hash = :th AND used_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ua', $usedAt);
        $stmt->bindParam(':th', $hash);
        return $stmt->execute();
    }
}
