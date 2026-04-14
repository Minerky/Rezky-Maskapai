<?php
/**
 * Koneksi database PDO — Rezky Maskapai
 * Sesuaikan host, nama DB, dan user bila perlu.
 * Password MySQL: biarkan string kosong (tanpa sandi) — umum di XAMPP lokal untuk user root.
 */

declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbName = 'rezky_maskapai';
$dbUser = 'root';
$dbPass = ''; // kosong = koneksi tanpa password MySQL
$charset = 'utf8mb4';

// URL dasar aplikasi (ubah jika folder/port berbeda)
define('BASE_URL', 'http://localhost/rezky');
define('PUBLIC_PATH', '/rezky');

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa config/database.php dan pastikan MySQL berjalan.');
}
