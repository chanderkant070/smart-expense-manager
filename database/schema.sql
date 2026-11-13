-- Smart Expense Manager database schema (MySQL 5.7+ / MariaDB 10.3+)
-- Tables are created automatically on first visit if they do not exist.

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  color CHAR(7) NOT NULL DEFAULT '#5b63ff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  sub_category VARCHAR(80) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  spent_on DATE NOT NULL,
  mode ENUM('UPI','Card','Cash','Bank') NOT NULL DEFAULT 'UPI',
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_date (user_id, spent_on),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS income (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  source VARCHAR(80) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  received_on DATE NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_date (user_id, received_on),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS goals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  title VARCHAR(80) NOT NULL,
  target DECIMAL(12,2) NOT NULL,
  saved DECIMAL(12,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO categories (name, color) VALUES
  ('Food & Grocery', '#5b63ff'),
  ('Shopping', '#16a34a'),
  ('Travelling', '#a855f7'),
  ('Bill & Subscription', '#22d3ee'),
  ('Entertainment', '#f97316'),
  ('Health', '#ef4444'),
  ('Education', '#eab308'),
  ('Investment', '#0ea5e9'),
  ('Miscellaneous', '#94a3b8');
