<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('user/index.php'));
}
require_post_csrf();

$flightId = (int) ($_POST['flight_id'] ?? 0);
$seatCount = (int) ($_POST['seat_count'] ?? 0);
$paymentMethod = trim((string) ($_POST['payment_method'] ?? ''));
$names = $_POST['p_name'] ?? [];
$ids = $_POST['p_id'] ?? [];
$dobs = $_POST['p_dob'] ?? [];

$userId = (int) $_SESSION['user']['id'];

if ($flightId <= 0 || $seatCount < 1 || !valid_payment_method($paymentMethod)) {
    flash('error', 'Data tidak valid.');
    redirect(url('user/index.php'));
}

$st = $pdo->prepare("SELECT id, price, status FROM flights WHERE id = ? AND status = 'active' LIMIT 1");
$st->execute([$flightId]);
$flight = $st->fetch();
if (!$flight) {
    flash('error', 'Penerbangan tidak ditemukan.');
    redirect(url('user/index.php'));
}

$avail = flight_available_seats($pdo, $flightId);
if ($seatCount > $avail || $seatCount > 8) {
    flash('error', 'Jumlah kursi melebihi ketersediaan.');
    redirect(url('user/booking.php?flight_id=' . $flightId));
}

$passengers = [];
for ($i = 0; $i < $seatCount; $i++) {
    $nm = trim((string) ($names[$i] ?? ''));
    $idn = trim((string) ($ids[$i] ?? ''));
    $dob = trim((string) ($dobs[$i] ?? ''));
    if (strlen($nm) < 3) {
        flash('error', 'Nama penumpang ' . ($i + 1) . ' minimal 3 karakter.');
        redirect(url('user/booking.php?flight_id=' . $flightId));
    }
    if (strlen($idn) < 5) {
        flash('error', 'Nomor identitas penumpang ' . ($i + 1) . ' tidak valid.');
        redirect(url('user/booking.php?flight_id=' . $flightId));
    }
    $dobSql = null;
    if ($dob !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $dobSql = $dob;
    }
    $passengers[] = ['name' => $nm, 'id_number' => $idn, 'dob' => $dobSql];
}

$total = round((float) $flight['price'] * $seatCount, 2);
$bookingCode = generate_booking_code($pdo);
$ticketCode = generate_ticket_code($pdo);

try {
    $pdo->beginTransaction();

    $insB = $pdo->prepare(
        'INSERT INTO bookings (user_id, flight_id, booking_code, seat_count, total_amount, status)
         VALUES (?,?,?,?,?,?)'
    );
    $insB->execute([$userId, $flightId, $bookingCode, $seatCount, $total, 'pending_payment']);
    $bookingId = (int) $pdo->lastInsertId();

    $insP = $pdo->prepare(
        'INSERT INTO booking_passengers (booking_id, full_name, id_number, date_of_birth) VALUES (?,?,?,?)'
    );
    foreach ($passengers as $p) {
        $insP->execute([$bookingId, $p['name'], $p['id_number'], $p['dob']]);
    }

    $insPay = $pdo->prepare(
        'INSERT INTO payments (booking_id, amount, payment_method, status) VALUES (?,?,?,?)'
    );
    $insPay->execute([$bookingId, $total, $paymentMethod, 'pending']);

    $insT = $pdo->prepare(
        'INSERT INTO tickets (booking_id, user_id, ticket_code, status) VALUES (?,?,?,?)'
    );
    $insT->execute([$bookingId, $userId, $ticketCode, 'inactive']);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', 'Gagal menyimpan pesanan. Coba lagi.');
    redirect(url('user/booking.php?flight_id=' . $flightId));
}

flash('success', 'Pesanan dibuat. Silakan lakukan pembayaran dan unggah bukti.');
redirect(url('user/payment.php?booking_id=' . $bookingId));
