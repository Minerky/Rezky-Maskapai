-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Apr 2026 pada 02.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rezky_maskapai`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `airlines`
--

CREATE TABLE `airlines` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(5) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `airlines`
--

INSERT INTO `airlines` (`id`, `name`, `code`, `created_at`) VALUES
(1, 'Garuda Indonesia', 'GA', '2026-04-13 07:26:41'),
(2, 'Lion Air', 'JT', '2026-04-13 07:26:41'),
(3, 'Batik Air', 'ID', '2026-04-13 07:26:41'),
(4, 'Citilink', 'QG', '2026-04-13 07:26:41'),
(5, 'Super Air Jet', 'IU', '2026-04-13 07:26:41'),
(6, 'Hana Pesawat', 'HA', '2026-04-13 13:48:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `airports`
--

CREATE TABLE `airports` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(150) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Indonesia',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `airports`
--

INSERT INTO `airports` (`id`, `code`, `name`, `city`, `country`, `created_at`) VALUES
(1, 'CGK', 'Soekarno-Hatta', 'Jakarta', 'Indonesia', '2026-04-13 07:26:41'),
(2, 'DPS', 'I Gusti Ngurah Rai', 'Denpasar', 'Indonesia', '2026-04-13 07:26:41'),
(3, 'SUB', 'Juanda', 'Surabaya', 'Indonesia', '2026-04-13 07:26:41'),
(4, 'UPG', 'Hasanuddin', 'Makassar', 'Indonesia', '2026-04-13 07:26:41'),
(5, 'YIA', 'Yogyakarta International', 'Kulon Progo', 'Indonesia', '2026-04-13 07:26:41'),
(6, 'BPN', 'Sepinggan', 'Balikpapan', 'Indonesia', '2026-04-13 07:26:41'),
(7, 'TKY', 'Tokyo International Airport', 'Tokyo', 'Jepang', '2026-04-13 13:49:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `flight_id` int(10) UNSIGNED NOT NULL,
  `booking_code` varchar(20) NOT NULL,
  `seat_count` smallint(5) UNSIGNED NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending_payment','awaiting_verification','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending_payment',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `flight_id`, `booking_code`, `seat_count`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'BK260413C67534', 1, 1850000.00, 'confirmed', '2026-04-13 07:32:45', '2026-04-13 07:33:42'),
(2, 5, 3, 'BK260413ABA785', 1, 1200000.00, 'confirmed', '2026-04-13 08:12:56', '2026-04-13 08:14:14'),
(3, 2, 6, 'BK260413AE1176', 1, 920000.00, 'confirmed', '2026-04-13 10:10:13', '2026-04-13 10:11:15'),
(4, 4, 6, 'BK260413D91032', 1, 920000.00, 'confirmed', '2026-04-13 10:58:59', '2026-04-13 10:59:59'),
(5, 2, 3, 'BK26041332391E', 1, 1200000.00, 'rejected', '2026-04-13 11:13:46', '2026-04-13 11:14:14'),
(6, 4, 3, 'BK260413582300', 1, 1200000.00, 'pending_payment', '2026-04-13 11:17:22', NULL),
(7, 4, 4, 'BK2604133C6B81', 2, 1900000.00, 'confirmed', '2026-04-13 11:18:19', '2026-04-13 11:19:18'),
(8, 7, 6, 'BK26041369C81B', 2, 1820000.00, 'confirmed', '2026-04-13 13:46:37', '2026-04-13 13:51:51'),
(9, 9, 5, 'BK2604133C7C00', 2, 2200000.00, 'confirmed', '2026-04-13 20:03:43', '2026-04-13 20:04:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_passengers`
--

CREATE TABLE `booking_passengers` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_passengers`
--

INSERT INTO `booking_passengers` (`id`, `booking_id`, `full_name`, `id_number`, `date_of_birth`, `created_at`) VALUES
(1, 1, 'Rezky', '3432432432423432', '2026-04-13', '2026-04-13 07:32:45'),
(2, 2, 'Dilan', '123891327981212', '2026-04-13', '2026-04-13 08:12:56'),
(3, 3, 'Rezkyyy', '23231331313', '2026-04-13', '2026-04-13 10:10:13'),
(4, 4, 'sdadsada', '31231313123123', '2026-04-13', '2026-04-13 10:58:59'),
(5, 5, 'testt', '123231231312', '2026-04-13', '2026-04-13 11:13:46'),
(6, 6, 'sdada', 'sdasdasdsadasd', '2026-04-13', '2026-04-13 11:17:22'),
(7, 7, 'Hanna', '31717132213121', '2026-04-13', '2026-04-13 11:18:19'),
(8, 7, 'Pais', '23442343223432432', '2026-04-13', '2026-04-13 11:18:19'),
(9, 8, 'Hanna', '31712323912361', '2026-04-13', '2026-04-13 13:46:37'),
(10, 8, 'Pais', '31729319723121', '2026-04-13', '2026-04-13 13:46:37'),
(11, 9, 'Ahmad Anthoni', '3171129129129129', '2026-04-13', '2026-04-13 20:03:43'),
(12, 9, 'Elgin', '312213123213123', '2026-04-13', '2026-04-13 20:03:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `flights`
--

CREATE TABLE `flights` (
  `id` int(10) UNSIGNED NOT NULL,
  `airline_id` smallint(5) UNSIGNED NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `origin_airport_id` smallint(5) UNSIGNED NOT NULL,
  `destination_airport_id` smallint(5) UNSIGNED NOT NULL,
  `departure_datetime` datetime NOT NULL,
  `arrival_datetime` datetime NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `seat_capacity` smallint(5) UNSIGNED NOT NULL DEFAULT 180,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `flights`
--

INSERT INTO `flights` (`id`, `airline_id`, `flight_number`, `origin_airport_id`, `destination_airport_id`, `departure_datetime`, `arrival_datetime`, `price`, `seat_capacity`, `status`, `created_at`) VALUES
(1, 1, 'GA-401', 1, 2, '2026-04-13 08:00:00', '2026-04-13 11:00:00', 1850000.00, 180, 'active', '2026-04-13 07:26:41'),
(2, 2, 'JT-612', 1, 3, '2026-04-13 10:00:00', '2026-04-13 12:00:00', 890000.00, 189, 'active', '2026-04-13 07:26:41'),
(3, 3, 'ID-205', 1, 5, '2026-04-14 06:00:00', '2026-04-14 07:20:00', 1200000.00, 162, 'active', '2026-04-13 07:26:41'),
(4, 4, 'QG-881', 3, 1, '2026-04-15 14:00:00', '2026-04-15 16:10:00', 950000.00, 180, 'active', '2026-04-13 07:26:41'),
(5, 5, 'IU-330', 2, 4, '2026-04-16 09:00:00', '2026-04-16 12:30:00', 1100000.00, 189, 'active', '2026-04-13 07:26:41'),
(6, 2, 'JT-708', 2, 1, '2026-04-17 19:00:00', '2026-04-17 21:15:00', 910000.00, 200, 'active', '2026-04-13 07:26:41'),
(7, 6, 'HA-3122', 1, 7, '2026-04-13 15:52:00', '2026-04-14 19:51:00', 100000000.00, 200, 'active', '2026-04-13 13:51:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(32) NOT NULL DEFAULT 'bank_transfer',
  `status` enum('pending','awaiting_verification','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_method`, `status`, `admin_note`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1850000.00, 'bank_transfer', 'approved', NULL, 1, '2026-04-13 07:33:42', '2026-04-13 07:32:45', '2026-04-13 07:33:42'),
(2, 2, 1200000.00, 'ewallet', 'approved', 'ok', 1, '2026-04-13 08:14:14', '2026-04-13 08:12:56', '2026-04-13 08:14:14'),
(3, 3, 920000.00, 'ewallet', 'approved', NULL, 1, '2026-04-13 10:11:15', '2026-04-13 10:10:13', '2026-04-13 10:11:15'),
(4, 4, 920000.00, 'bank_transfer', 'approved', NULL, 1, '2026-04-13 10:59:59', '2026-04-13 10:58:59', '2026-04-13 10:59:59'),
(5, 5, 1200000.00, 'bank_transfer', 'rejected', NULL, 1, '2026-04-13 11:14:14', '2026-04-13 11:13:46', '2026-04-13 11:14:14'),
(6, 6, 1200000.00, 'bank_transfer', 'pending', NULL, NULL, NULL, '2026-04-13 11:17:22', NULL),
(7, 7, 1900000.00, 'bank_transfer', 'approved', NULL, 1, '2026-04-13 11:19:18', '2026-04-13 11:18:19', '2026-04-13 11:19:18'),
(8, 8, 1820000.00, 'bank_transfer', 'approved', NULL, 1, '2026-04-13 13:51:51', '2026-04-13 13:46:37', '2026-04-13 13:51:51'),
(9, 9, 2200000.00, 'bank_transfer', 'approved', 'oke amjut', 1, '2026-04-13 20:04:20', '2026-04-13 20:03:43', '2026-04-13 20:04:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payment_proofs`
--

INSERT INTO `payment_proofs` (`id`, `payment_id`, `stored_filename`, `original_filename`, `mime_type`, `file_size`, `created_at`) VALUES
(1, 1, 'proof_e3fb81f2553f7f36f9539b07.png', 'Screenshot_2.png', 'image/png', 127264, '2026-04-13 07:32:55'),
(2, 2, 'proof_4a4a637a671314e70b7d87b7.png', 'Screenshot_7.png', 'image/png', 288244, '2026-04-13 08:13:03'),
(3, 3, 'proof_9358701a7060429829bc854e.png', 'Screenshot_8.png', 'image/png', 648236, '2026-04-13 10:10:45'),
(4, 4, 'proof_9b4a76de819938610594efe6.png', 'Screenshot_7.png', 'image/png', 288244, '2026-04-13 10:59:07'),
(5, 5, 'proof_6ec622108c732ded1732300b.png', 'Screenshot_15.png', 'image/png', 642178, '2026-04-13 11:13:52'),
(6, 7, 'proof_33fd9d0826e70376259e4ed5.jpg', 'sms permat.jpeg', 'image/jpeg', 110744, '2026-04-13 11:18:57'),
(7, 8, 'proof_74939b813de403adc6b74cb4.jpg', 'sms permat.jpeg', 'image/jpeg', 110744, '2026-04-13 13:47:00'),
(8, 9, 'proof_64f11fe6f6ea446343353479.jpg', 'b438e95e4e6603e07901f0e94dd8c8ed.jpg', 'image/jpeg', 57797, '2026-04-13 20:04:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`) VALUES
(1, 'Administrator', 'admin'),
(2, 'Penumpang', 'user');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `ticket_code` varchar(24) NOT NULL,
  `status` enum('inactive','active','cancelled') NOT NULL DEFAULT 'inactive',
  `issued_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `booking_id`, `user_id`, `ticket_code`, `status`, `issued_at`, `created_at`) VALUES
(1, 1, 4, 'TK260471986CF7', 'active', '2026-04-13 07:33:42', '2026-04-13 07:32:45'),
(2, 2, 5, 'TK260485B8A8EB', 'active', '2026-04-13 08:14:14', '2026-04-13 08:12:56'),
(3, 3, 2, 'TK2604A7D3CC76', 'active', '2026-04-13 10:11:15', '2026-04-13 10:10:13'),
(4, 4, 4, 'TK26044A8C5B28', 'active', '2026-04-13 10:59:59', '2026-04-13 10:58:59'),
(5, 5, 2, 'TK2604A9225474', 'cancelled', NULL, '2026-04-13 11:13:46'),
(6, 6, 4, 'TK2604CAF8AE26', 'inactive', NULL, '2026-04-13 11:17:22'),
(7, 7, 4, 'TK2604C6933148', 'active', '2026-04-13 11:19:18', '2026-04-13 11:18:19'),
(8, 8, 7, 'TK260466DD776A', 'active', '2026-04-13 13:51:51', '2026-04-13 13:46:37'),
(9, 9, 9, 'TK2604275AAB98', 'active', '2026-04-13 20:04:20', '2026-04-13 20:03:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `nik`, `password_hash`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin Rezky', 'admin@rezky.test', '08110000001', '31171717171177717', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.', 1, '2026-04-13 07:26:41', '2026-04-13 20:32:39'),
(2, 2, 'Budi Penumpang', 'user@rezky.test', '08120000002', '31171717171177716', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.', 1, '2026-04-13 07:26:41', '2026-04-13 20:33:11'),
(3, 2, 'Siti Traveler', 'siti@rezky.test', '08130000003', '31171717171177715', '$2y$10$O1LJecBS.Ko7RzqJMJqiROBqhdNyekgFFm4lh7S64MR.FX39FmbP.', 1, '2026-04-13 07:26:41', '2026-04-13 20:33:14'),
(4, 2, 'Muhammad Rezky', 'boncici05@gmail.com', '085891028145', '3171040312051001', '$2y$10$v7XOL1M7PH41W5b0vtsx/.FtfZoNnMKH.EpndlLGD5RwoBLtYqRT6', 1, '2026-04-13 07:31:32', '2026-04-13 19:49:27'),
(5, 2, 'Sanders Mitcheel Ruung', 'Sanders@gmail.com', '081910645686', '31171717171177714', '$2y$10$ujipAkdjGJNlYaQnSb4xp.2nhvY/q8kFPMoCyeO3KwD4Zualzrf76', 1, '2026-04-13 08:11:12', '2026-04-13 20:33:17'),
(6, 2, 'Aldyan Saputra', 'aldyan@gmail.com', '088888777876', '31171717171177711', '$2y$10$WzPHyxkGaX/AEL1M/5I.fulnqVJuZi08qHn7HR.4K6b5Ba0ONBcEm', 1, '2026-04-13 13:09:30', '2026-04-13 20:33:41'),
(7, 2, 'Hanna', 'hanna@gmail.com', '08717761616166', '3117171717117772', '$2y$10$HfPnx7C7ATjDVXvZBudbF.JkgUAVXLyNst1X9OfFxRiL15LxlHFrq', 1, '2026-04-13 13:38:32', '2026-04-13 20:33:38'),
(8, 2, 'Gunawan Madya', 'gunawan@gmail.com', '08877879876888', '3127123729347927', '$2y$10$3jud9Pa6dXWQiKbP.p1ETuX.Lmi.riTVsLcSjrsLnnfz1TJePFf6i', 1, '2026-04-13 13:58:20', NULL),
(9, 2, 'Ahmad Anthoni', 'anton@gmail.com', '088887877876', '3171129129129129', '$2y$10$w114b9DunhTTYIA2Z.wNhuFhZyk8HyMzoCsZ3tDqB4hpOAzlG61vu', 1, '2026-04-13 20:02:55', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `airlines`
--
ALTER TABLE `airlines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_airlines_code` (`code`);

--
-- Indeks untuk tabel `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_airports_code` (`code`);

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_bookings_code` (`booking_code`),
  ADD KEY `fk_bookings_user` (`user_id`),
  ADD KEY `fk_bookings_flight` (`flight_id`),
  ADD KEY `idx_bookings_status` (`status`);

--
-- Indeks untuk tabel `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bp_booking` (`booking_id`);

--
-- Indeks untuk tabel `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_flights_airline` (`airline_id`),
  ADD KEY `fk_flights_origin` (`origin_airport_id`),
  ADD KEY `fk_flights_dest` (`destination_airport_id`),
  ADD KEY `idx_flights_dep` (`departure_datetime`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payments_booking` (`booking_id`),
  ADD KEY `fk_payments_verifier` (`verified_by`);

--
-- Indeks untuk tabel `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pp_payment` (`payment_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_slug` (`slug`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tickets_code` (`ticket_code`),
  ADD KEY `fk_tickets_booking` (`booking_id`),
  ADD KEY `fk_tickets_user` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_nik` (`nik`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `airlines`
--
ALTER TABLE `airlines`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `airports`
--
ALTER TABLE `airports`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `booking_passengers`
--
ALTER TABLE `booking_passengers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`),
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD CONSTRAINT `fk_bp_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `fk_flights_airline` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`id`),
  ADD CONSTRAINT `fk_flights_dest` FOREIGN KEY (`destination_airport_id`) REFERENCES `airports` (`id`),
  ADD CONSTRAINT `fk_flights_origin` FOREIGN KEY (`origin_airport_id`) REFERENCES `airports` (`id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `fk_pp_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
