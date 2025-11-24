CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) UNIQUE,
  role ENUM('admin', 'user'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100),
  contact VARCHAR(100),
  id_number VARCHAR(100),
  usdt_in DECIMAL(10,2),
  usdt_out DECIMAL(10,2),
  usd_in DECIMAL(10,2),
  usd_out DECIMAL(10,2),
  ugx_in DECIMAL(15,2),
  ugx_out DECIMAL(15,2),
  usd_balance DECIMAL(10,2),
  ugx_balance DECIMAL(15,2),
  expenditure DECIMAL(15,2),
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
  id INT PRIMARY KEY,
  usdt_to_ugx DECIMAL(10,2),
  usd_to_usdt DECIMAL(10,2)
);

INSERT INTO settings (id, usdt_to_ugx, usd_to_usdt) VALUES (1, 3500, 1);
INSERT INTO users (email, role) VALUES ('admin@exchange.com', 'admin');
