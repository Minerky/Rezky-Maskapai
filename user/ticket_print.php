<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$bookingId = (int) ($_GET['booking_id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];
if ($bookingId <= 0) {
    redirect(url('user/history.php'));
}

$sql = "SELECT b.*, u.full_name AS booker_name, u.email AS booker_email,
               t.ticket_code, t.status AS ticket_status,
               pay.payment_method,
               f.flight_number, f.departure_datetime, f.arrival_datetime,
               al.name AS airline_name, al.code AS airline_code,
               ao.city AS origin_city, ao.code AS origin_code, ao.name AS origin_name,
               ad.city AS dest_city, ad.code AS dest_code, ad.name AS dest_name
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        JOIN tickets t ON t.booking_id = b.id
        JOIN payments pay ON pay.booking_id = b.id
        JOIN flights f ON f.id = b.flight_id
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed' LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$bookingId, $userId]);
$d = $st->fetch();
if (!$d || $d['ticket_status'] !== 'active') {
    flash('error', 'E-ticket hanya tersedia setelah pembayaran disetujui.');
    redirect(url('user/payment.php?booking_id=' . $bookingId));
}

$stP = $pdo->prepare('SELECT full_name, id_number FROM booking_passengers WHERE booking_id = ? ORDER BY id');
$stP->execute([$bookingId]);
$passengers = $stP->fetchAll();

$pageTitle = 'E-Ticket ' . $d['ticket_code'] . ' — Rezky Maskapai';

$depTs = strtotime($d['departure_datetime']);
$arrTs = strtotime($d['arrival_datetime']);
$durationMin = max(0, (int) round(($arrTs - $depTs) / 60));
$durH = intdiv($durationMin, 60);
$durM = $durationMin % 60;
if ($durH > 0) {
    $durationLabel = $durM > 0 ? $durH . ' j ' . $durM . ' m' : $durH . ' jam';
} else {
    $durationLabel = $durM . ' menit';
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card no-print eticket-toolbar">
    <div class="eticket-toolbar-inner">
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
        <p class="eticket-toolbar-hint muted">Gunakan &quot;Simpan sebagai PDF&quot; di dialog cetak browser bila ingin file PDF.</p>
    </div>
</div>

<div class="ticket-print eticket" id="eticket">
    <header class="eticket-header">
        <div class="eticket-header-main">
            <p class="eticket-kicker">Boarding pass digital</p>
            <h1 class="eticket-title">E‑ticket</h1>
            <p class="eticket-subtitle">Rezky Maskapai</p>
        </div>
        <div class="eticket-header-codes">
            <div class="eticket-code-block">
                <span class="eticket-code-label">Kode tiket</span>
                <code class="eticket-code-value"><?= h($d['ticket_code']) ?></code>
            </div>
            <div class="eticket-code-block">
                <span class="eticket-code-label">Kode booking</span>
                <span class="eticket-booking-code"><?= h($d['booking_code']) ?></span>
            </div>
        </div>
    </header>

    <div class="eticket-accent-bar" aria-hidden="true"></div>

    <section class="eticket-section" aria-label="Detail penerbangan">
        <p class="booking-detail-airline"><?= h($d['airline_name']) ?> <?= h($d['flight_number']) ?></p>
        <p class="booking-detail-airline-meta">
            <span class="flight-detail-badge"><?= h($d['airline_code']) ?></span>
            <span class="muted">·</span>
            <span class="muted">Durasi perkiraan <?= h($durationLabel) ?></span>
        </p>

        <div class="flight-route flight-detail-route eticket-route" role="group" aria-label="Bandara asal dan tujuan">
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Asal</span>
                <strong class="flight-endpoint-place"><?= h($d['origin_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($d['origin_code']) ?>)</span>
            </div>
            <span class="flight-route-arrow" aria-hidden="true">→</span>
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Tujuan</span>
                <strong class="flight-endpoint-place"><?= h($d['dest_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($d['dest_code']) ?>)</span>
            </div>
        </div>
        <p class="flight-detail-airport-names eticket-airport-names"><?= h($d['origin_name']) ?> → <?= h($d['dest_name']) ?></p>

        <div class="flight-detail-times eticket-times">
            <div class="flight-time-card flight-time-card--depart">
                <span class="flight-time-card-label">Keberangkatan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', $depTs)) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', $depTs)) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', $depTs)) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($d['origin_name']) ?></span>
            </div>
            <div class="flight-time-card flight-time-card--arrive">
                <span class="flight-time-card-label">Kedatangan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', $arrTs)) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', $arrTs)) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', $arrTs)) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($d['dest_name']) ?></span>
            </div>
        </div>
    </section>

    <div class="flight-detail-stats eticket-summary">
        <div class="flight-stat">
            <span class="flight-stat-label">Kursi</span>
            <span class="flight-stat-value"><?= (int) $d['seat_count'] ?> penumpang</span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Total dibayar</span>
            <span class="flight-stat-value flight-stat-price">Rp <?= h(number_format((float) $d['total_amount'], 0, ',', '.')) ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Pembayaran</span>
            <span class="flight-stat-value"><?= h(payment_method_label((string) ($d['payment_method'] ?? 'bank_transfer'))) ?></span>
        </div>
    </div>

    <section class="eticket-section eticket-passengers-section" aria-labelledby="eticket-passengers-title">
        <h2 id="eticket-passengers-title" class="eticket-section-title">Daftar penumpang</h2>
        <ol class="eticket-passenger-list">
            <?php foreach ($passengers as $i => $p): ?>
                <li class="eticket-passenger-row">
                    <span class="eticket-passenger-idx"><?= $i + 1 ?></span>
                    <div class="eticket-passenger-info">
                        <strong class="eticket-passenger-name"><?= h($p['full_name']) ?></strong>
                        <span class="eticket-passenger-id"><?= h($p['id_number']) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <footer class="eticket-footer">
        <p class="eticket-booker"><strong>Pemesan:</strong> <?= h($d['booker_name']) ?> · <?= h($d['booker_email']) ?></p>
        <p class="eticket-disclaimer">Dokumen ini sah sebagai bukti pembayaran yang telah diverifikasi. Tunjukkan identitas yang sesuai saat check-in.</p>
    </footer>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
