<?php
class Appointment {
    private $conn;
    private $table = 'appointments';

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(50) NOT NULL,
            `service` VARCHAR(50) NOT NULL,
            `date` DATE NOT NULL,
            `time` VARCHAR(10) NOT NULL,
            `message` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->conn->exec($sql);
    }

    public function create($data) {
        $sql = "INSERT INTO `{$this->table}`
            (`full_name`, `email`, `phone`, `service`, `date`, `time`, `message`)
            VALUES (:full_name, :email, :phone, :service, :date, :time, :message)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name' => $data['fullName'],
            ':email'     => $data['email'],
            ':phone'     => $data['phone'],
            ':service'   => $data['service'],
            ':date'      => $data['date'],
            ':time'      => $data['time'],
            ':message'   => $data['message'] ?? null,
        ]);
    }
}
