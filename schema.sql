CREATE DATABASE IF NOT EXISTS lostfound;
USE lostfound;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100), email VARCHAR(100),
  password VARCHAR(255),
  role ENUM('student','admin') DEFAULT 'student');

CREATE TABLE items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(150),
  description TEXT,
  location VARCHAR(150),
  status ENUM('lost','found','returned') DEFAULT 'found',
  user_id INT, 
  image VARCHAR(255),
  date_reported DATETIME DEFAULT CURRENT_TIMESTAMP,
  approved TINYINT(1) DEFAULT 0,
  removed TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL);

INSERT INTO users (name, email, password, role) VALUES 
  ('Admin User','admin@muj.manipal.edu',SHA2('admin123',256),'admin'),
  ('Test Student','student1@muj.manipal.edu',SHA2('student123',256),'student');

-- Index for search
CREATE INDEX idx_items_name_loc ON items(item_name(50), location(50));

CREATE TABLE IF NOT EXISTS messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  subject VARCHAR(255),
  body TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(item_id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
