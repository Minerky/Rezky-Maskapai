<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/transactions.php'));
}
require_post_csrf();

$bookingId = (int) ($_POST['booking_id'] ?? 0);
$decision = (string) ($_POST['decision'] ?? '');
$adminNote = trim((string) ($_POST['admin_note'] ?? ''));
$adminId = (int) $_SESSION['user']['id'];

if ($bookingId < 1 || !in_array($decision, ['approve', 'reject'], true)) {
    flash('error', 'Permintaan tidak valid.');
    redirect(url('admin/transactions.php'));
}

$sql = "SELECT b.id, b.flight_id, b.seat_count, b.status AS bstatus,
               p.id AS payment_id, p.status AS pstatus
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        WHERE b.id = ? LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$bookingId]);
$b = $st->fetch();
if (!$b || $b['bstatus'] !== 'awaiting_verification' || $b['pstatus'] !== 'awaiting_verification') {
    flash('error', 'Pesanan tidak dalam status verifikasi.');
    redirect(url('admin/transactions.php'));
}

try {
    $pdo->beginTransaction();
    if ($decision === 'approve') {
        $pdo->prepare(
            "UPDATE payments SET status='approved', admin_note=?, verified_by=?, verified_at=NOW() WHERE id=?"
        )->execute([$adminNote ?: null, $adminId, (int) $b['payment_id']]);
        $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$bookingId]);
        $pdo->prepare("UPDATE tickets SET status='active', issued_at=NOW() WHERE booking_id=?")->execute([$bookingId]);
        flash('success', 'Pembayaran disetujui. Tiket aktif.');
    } else {
        $pdo->prepare(
            "UPDATE payments SET status='rejected', admin_note=?, verified_by=?, verified_at=NOW() WHERE id=?"
        )->execute([$adminNote ?: null, $adminId, (int) $b['payment_id']]);
        $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=?")->execute([$bookingId]);
        $pdo->prepare("UPDATE tickets SET status='cancelled' WHERE booking_id=?")->execute([$bookingId]);
        flash('success', 'Pembayaran ditolak.');
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', 'Gagal memproses.');
}

redirect(url('admin/transactions.php'));
