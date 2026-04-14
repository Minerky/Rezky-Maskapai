<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('user/history.php'));
}
require_post_csrf();

$bookingId = (int) ($_POST['booking_id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];
$maxBytes = 2 * 1024 * 1024;

if ($bookingId <= 0 || empty($_FILES['proof']) || !isset($_FILES['proof']['error'])) {
    flash('error', 'Unggahan tidak valid.');
    redirect(url('user/history.php'));
}

$file = $_FILES['proof'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'Gagal mengunggah file.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

if ($file['size'] > $maxBytes) {
    flash('error', 'Ukuran file melebihi 2 MB.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

if (!class_exists('finfo')) {
    flash('error', 'Server memerlukan ekstensi PHP fileinfo untuk validasi unggahan.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']) ?: '';
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf',
];
if (!isset($allowed[$mime])) {
    flash('error', 'Tipe file tidak diizinkan (hanya JPG, PNG, PDF).');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

$sql = "SELECT b.id, b.status AS bstatus, p.id AS payment_id, p.status AS pstatus
        FROM bookings b JOIN payments p ON p.booking_id = b.id
        WHERE b.id = ? AND b.user_id = ? LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$bookingId, $userId]);
$b = $st->fetch();
if (!$b) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(url('user/history.php'));
}

$okState = in_array($b['bstatus'], ['pending_payment', 'awaiting_verification'], true)
    && in_array($b['pstatus'], ['pending', 'awaiting_verification'], true);
if (!$okState) {
    flash('error', 'Status pesanan tidak mengizinkan unggahan.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

$ext = $allowed[$mime];
$dir = __DIR__ . '/../uploads/proofs/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$stored = 'proof_' . bin2hex(random_bytes(12)) . '.' . $ext;
$dest = $dir . $stored;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    flash('error', 'Gagal menyimpan file.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

$paymentId = (int) $b['payment_id'];

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM payment_proofs WHERE payment_id = ?')->execute([$paymentId]);

    $ins = $pdo->prepare(
        'INSERT INTO payment_proofs (payment_id, stored_filename, original_filename, mime_type, file_size)
         VALUES (?,?,?,?,?)'
    );
    $ins->execute([
        $paymentId,
        $stored,
        function_exists('mb_substr') ? mb_substr($file['name'], 0, 200, 'UTF-8') : substr($file['name'], 0, 200),
        $mime,
        (int) $file['size'],
    ]);

    $pdo->prepare("UPDATE payments SET status = 'awaiting_verification' WHERE id = ?")->execute([$paymentId]);
    $pdo->prepare("UPDATE bookings SET status = 'awaiting_verification' WHERE id = ?")->execute([$bookingId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    @unlink($dest);
    flash('error', 'Gagal menyimpan data bukti.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

flash('success', 'Bukti berhasil diunggah. Menunggu verifikasi admin.');
redirect(url('user/payment.php?booking_id=' . $bookingId));
