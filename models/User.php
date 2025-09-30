<?php
require_once './config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $phone;
    public $address;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET username=:username, email=:email, password=:password, 
                     first_name=:first_name, last_name=:last_name, 
                     phone=:phone, address=:address";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Login user
    public function login($username, $password) {
        $query = "SELECT id, username, email, password, first_name, last_name 
                 FROM " . $this->table_name . " 
                 WHERE username = :username OR email = :username LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($password, $row['password'])) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            
            return true;
        }
        
        return false;
    }

    // Check if username exists
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return true;
        }
        
        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return true;
        }
        
        return false;
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }

    // Update user profile
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                 SET first_name=:first_name, last_name=:last_name, 
                     phone=:phone, address=:address 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>