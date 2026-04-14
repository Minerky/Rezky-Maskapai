-- Rezky Maskapai - skema database lengkap
-- Impor lewat phpMyAdmin atau: mysql -u root < database/rezky_maskapai.sql
-- Kredensial demo: admin@rezky.test / user@rezky.test -- sandi: password123

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS rezky_maskapai;
CREATE DATABASE rezky_maskapai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rezky_maskapai;

-- --------------------------------------------------------
-- roles
-- --------------------------------------------------------
CREATE TABLE roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB;

INSERT INTO roles (id, name, slug) VALUES
(1, 'Administrator', 'admin'),
(2, 'Penumpang', 'user');

-- --------------------------------------------------------
-- users
-- --------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id TINYINT UNSIGNED NOT NULL DEFAULT 2,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY fk_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB;

-- password123 (bcrypt)
INSERT INTO users (id, role_id, full_name, email, phone, password_hash) VALUES
(1, 1, 'Admin Rezky', 'admin@rezky.test', '08110000001', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.'),
(2, 2, 'Budi Penumpang', 'user@rezky.test', '08120000002', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.'),
(3, 2, 'Siti Traveler', 'siti@rezky.test', '08130000003', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.');

-- --------------------------------------------------------
-- airports
-- --------------------------------------------------------
CREATE TABLE airports (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(5) NOT NULL,
  name VARCHAR(150) NOT NULL,
  city VARCHAR(100) NOT NULL,
  country VARCHAR(100) NOT NULL DEFAULT 'Indonesia',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_airports_code (code)
) ENGINE=InnoDB;

INSERT INTO airports (code, name, city, country) VALUES
('CGK', 'Soekarno-Hatta', 'Jakarta', 'Indonesia'),
('DPS', 'I Gusti Ngurah Rai', 'Denpasar', 'Indonesia'),
('SUB', 'Juanda', 'Surabaya', 'Indonesia'),
('UPG', 'Hasanuddin', 'Makassar', 'Indonesia'),
('YIA', 'Yogyakarta International', 'Kulon Progo', 'Indonesia'),
('BPN', 'Sepinggan', 'Balikpapan', 'Indonesia');

-- --------------------------------------------------------
-- airlines
-- --------------------------------------------------------
CREATE TABLE airlines (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(5) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_airlines_code (code)
) ENGINE=InnoDB;

INSERT INTO airlines (name, code) VALUES
('Garuda Indonesia', 'GA'),
('Lion Air', 'JT'),
('Batik Air', 'ID'),
('Citilink', 'QG'),
('Super Air Jet', 'IU');

-- --------------------------------------------------------
-- flights
-- --------------------------------------------------------
CREATE TABLE flights (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  airline_id SMALLINT UNSIGNED NOT NULL,
  flight_number VARCHAR(20) NOT NULL,
  origin_airport_id SMALLINT UNSIGNED NOT NULL,
  destination_airport_id SMALLINT UNSIGNED NOT NULL,
  departure_datetime DATETIME NOT NULL,
  arrival_datetime DATETIME NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  seat_capacity SMALLINT UNSIGNED NOT NULL DEFAULT 180,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_flights_airline (airline_id),
  KEY fk_flights_origin (origin_airport_id),
  KEY fk_flights_dest (destination_airport_id),
  KEY idx_flights_dep (departure_datetime),
  CONSTRAINT fk_flights_airline FOREIGN KEY (airline_id) REFERENCES airlines (id),
  CONSTRAINT fk_flights_origin FOREIGN KEY (origin_airport_id) REFERENCES airports (id),
  CONSTRAINT fk_flights_dest FOREIGN KEY (destination_airport_id) REFERENCES airports (id)
) ENGINE=InnoDB;

-- Jadwal contoh (tanggal relatif ke hari ini diisi lewat aplikasi; di SQL pakai tanggal tetap untuk demo)
-- Jadwal demo tersebar hari ini s/d +10 hari agar mudah dicoba di localhost
INSERT INTO flights (airline_id, flight_number, origin_airport_id, destination_airport_id, departure_datetime, arrival_datetime, price, seat_capacity, status) VALUES
(1, 'GA-401', 1, 2, DATE_ADD(CURDATE(), INTERVAL 0 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 0 DAY) + INTERVAL 11 HOUR, 1850000.00, 180, 'active'),
(2, 'JT-612', 1, 3, DATE_ADD(CURDATE(), INTERVAL 0 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 0 DAY) + INTERVAL 12 HOUR, 890000.00, 189, 'active'),
(3, 'ID-205', 1, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 6 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 7 HOUR + INTERVAL 20 MINUTE, 1200000.00, 162, 'active'),
(4, 'QG-881', 3, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 16 HOUR + INTERVAL 10 MINUTE, 950000.00, 180, 'active'),
(5, 'IU-330', 2, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 9 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 12 HOUR + INTERVAL 30 MINUTE, 1100000.00, 189, 'active'),
(2, 'JT-708', 2, 1, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 19 HOUR, DATE_ADD(CURDATE(), INTERVAL 4 DAY) + INTERVAL 21 HOUR + INTERVAL 15 MINUTE, 920000.00, 189, 'active');

-- --------------------------------------------------------
-- bookings
-- --------------------------------------------------------
CREATE TABLE bookings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  flight_id INT UNSIGNED NOT NULL,
  booking_code VARCHAR(20) NOT NULL,
  seat_count SMALLINT UNSIGNED NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  status ENUM('pending_payment','awaiting_verification','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending_payment',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bookings_code (booking_code),
  KEY fk_bookings_user (user_id),
  KEY fk_bookings_flight (flight_id),
  KEY idx_bookings_status (status),
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_bookings_flight FOREIGN KEY (flight_id) REFERENCES flights (id)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- booking_passengers
-- --------------------------------------------------------
CREATE TABLE booking_passengers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  id_number VARCHAR(50) NOT NULL,
  date_of_birth DATE DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_bp_booking (booking_id),
  CONSTRAINT fk_bp_booking FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- payments
-- --------------------------------------------------------
CREATE TABLE payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_method VARCHAR(32) NOT NULL DEFAULT 'bank_transfer',
  status ENUM('pending','awaiting_verification','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(255) DEFAULT NULL,
  verified_by INT UNSIGNED DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_booking (booking_id),
  KEY fk_payments_verifier (verified_by),
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_verifier FOREIGN KEY (verified_by) REFERENCES users (id)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- payment_proofs
-- --------------------------------------------------------
CREATE TABLE payment_proofs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_id INT UNSIGNED NOT NULL,
  stored_filename VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_pp_payment (payment_id),
  CONSTRAINT fk_pp_payment FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- tickets
-- --------------------------------------------------------
CREATE TABLE tickets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  ticket_code VARCHAR(24) NOT NULL,
  status ENUM('inactive','active','cancelled') NOT NULL DEFAULT 'inactive',
  issued_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tickets_code (ticket_code),
  KEY fk_tickets_booking (booking_id),
  KEY fk_tickets_user (user_id),
  CONSTRAINT fk_tickets_booking FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE,
  CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
