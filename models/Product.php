<?php
require_once './config/database.php';

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $price;
    public $stock_quantity;
    public $category_id;
    public $image_url;
    public $brand;
    public $model;
    public $specifications;
    public $created_at;
    public $category_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products
    public function getAll() {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Get all products with pagination
    public function getAllPaginated($limit, $offset) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 ORDER BY p.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Count all products
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    // Get products by category
    public function getByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = ? 
                 ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Get products by category with pagination
    public function getByCategoryPaginated($category_id, $limit, $offset) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = :category_id 
                 ORDER BY p.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Count products by category
    public function countByCategory($category_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    // Get single product
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->category_id = $row['category_id'];
            $this->image_url = $row['image_url'];
            $this->brand = $row['brand'];
            $this->model = $row['model'];
            $this->specifications = $row['specifications'];
            $this->created_at = $row['created_at'];
            $this->category_name = $row['category_name'];
            
            return true;
        }
        
        return false;
    }

    // Search products
    public function search($keywords) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?
                 ORDER BY p.created_at DESC";
        
        $keywords = "%{$keywords}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        $stmt->execute();
        
        return $stmt;
    }

    // Search products with pagination
    public function searchPaginated($keywords, $limit, $offset) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $like = "%{$keywords}%";
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.name LIKE :kw OR p.description LIKE :kw OR p.brand LIKE :kw 
                 ORDER BY p.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':kw', $like, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Count search results
    public function countSearch($keywords) {
        $like = "%{$keywords}%";
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                 WHERE name LIKE :kw OR description LIKE :kw OR brand LIKE :kw";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':kw', $like, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    // Create product (admin function)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, 
                     stock_quantity=:stock_quantity, category_id=:category_id, 
                     image_url=:image_url, brand=:brand, model=:model, 
                     specifications=:specifications";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->model = htmlspecialchars(strip_tags($this->model));
        $this->specifications = htmlspecialchars(strip_tags($this->specifications));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":specifications", $this->specifications);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Update stock quantity
    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table_name . " 
                 SET stock_quantity = stock_quantity - ? 
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>