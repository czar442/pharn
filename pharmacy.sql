-- 1. Create database
CREATE DATABASE IF NOT EXISTS pharmacy;
USE pharmacy;

-- 2. Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default users
INSERT INTO users (name,email,password,role) VALUES
('Admin','admin@gmail.com',SHA2('abc123',256),'admin'),
('Manager','example@example.com',SHA2('abc123',256),'manager'),
('Manager','nawjeshbd@gmail.com',SHA2('abc123',256),'manager');

-- 3. Medicines table
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    category VARCHAR(100),
    quantity INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    expiry_date DATE,
    barcode VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample medicines
INSERT INTO medicines (name,brand,category,quantity,price,expiry_date,barcode) VALUES
('Paracetamol','BrandA','Tablet',50,0.5,'2026-12-31','1234567890123'),
('Amoxicillin','BrandB','Capsule',30,1.2,'2025-11-30','1234567890456'),
('Ibuprofen','BrandC','Tablet',20,0.8,'2025-12-15','1234567890789');

-- 4. Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample suppliers
INSERT INTO suppliers (name,email,phone,address) VALUES
('Supplier A','supA@gmail.com','0700123456','Kampala, Uganda'),
('Supplier B','supB@gmail.com','0700654321','Entebbe, Uganda');

-- 5. Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample customers
INSERT INTO customers (name,email,phone,address) VALUES
('Customer A','custA@gmail.com','0770123456','Kampala, Uganda'),
('Customer B','custB@gmail.com','0770654321','Jinja, Uganda');

-- 6. Sales table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id VARCHAR(50) NOT NULL,
    customer_id INT,
    total DECIMAL(10,2),
    paid DECIMAL(10,2),
    due DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- 7. Sale Items table
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    medicine_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
);

-- 8. Expenses table
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Purchase table
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    medicine_id INT,
    quantity INT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
);

-- 10. Settings table (optional)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) NOT NULL,
    value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
