-- Create database
CREATE DATABASE IF NOT EXISTS printer_store;
USE printer_store;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    image_url VARCHAR(500),
    brand VARCHAR(100),
    model VARCHAR(100),
    specifications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Inkjet Printers', 'High-quality inkjet printers for home and office use'),
('Laser Printers', 'Fast and efficient laser printers'),
('All-in-One Printers', 'Multifunction printers with scanning and copying'),
('Photo Printers', 'Specialized printers for photo printing');

-- Insert sample products
INSERT INTO products (name, description, price, stock_quantity, category_id, image_url, brand, model, specifications) VALUES
('Canon PIXMA TS3420', 'Wireless All-in-One Inkjet Printer with mobile printing', 79.99, 25, 3, 'https://via.placeholder.com/300x200/4CAF50/ffffff?text=Canon+PIXMA', 'Canon', 'PIXMA TS3420', 'Print, Copy, Scan | Wireless | Mobile Printing | 4800x1200 DPI'),
('HP - DeskJet 2855e Wireless AI-Enabled AiO Inkjet Printer w/ 3 Mo. of Instant Ink', 'Compact wireless laser printer perfect for small offices', 129.99, 15, 2, 'https://via.placeholder.com/300x200/2196F3/ffffff?text=HP+LaserJet', 'HP', 'LaserJet Pro M15w', 'Laser | Wireless | Mobile Printing | 600x600 DPI | 19 PPM'),
('Epson EcoTank ET-2720', 'All-in-one supertank printer with 2 years of ink included', 199.99, 20, 1, 'https://via.placeholder.com/300x200/FF9800/ffffff?text=Epson+EcoTank', 'Epson', 'EcoTank ET-2720', 'Inkjet | All-in-One | Supertank | Wireless | 5760x1440 DPI'),
('Brother HL-L2350DW', 'Compact monochrome laser printer with wireless connectivity', 99.99, 30, 2, 'https://via.placeholder.com/300x200/9C27B0/ffffff?text=Brother+Laser', 'Brother', 'HL-L2350DW', 'Monochrome Laser | Wireless | Duplex Printing | 2400x600 DPI | 32 PPM'),
('Canon SELPHY CP1300', 'Compact photo printer for instant photo printing', 149.99, 12, 4, 'https://via.placeholder.com/300x200/E91E63/ffffff?text=Canon+Photo', 'Canon', 'SELPHY CP1300', 'Dye Sublimation | Photo Printing | Wireless | 300x300 DPI | 4x6 inch prints'),
('HP OfficeJet Pro 9015e', 'All-in-one color inkjet printer for small businesses', 229.99, 18, 3, 'https://via.placeholder.com/300x200/607D8B/ffffff?text=HP+OfficeJet', 'HP', 'OfficeJet Pro 9015e', 'Color Inkjet | All-in-One | Wireless | Duplex | 1200x1200 DPI | 22 PPM');
