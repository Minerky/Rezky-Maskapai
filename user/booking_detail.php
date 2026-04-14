<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$id = (int) ($_GET['id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];
if ($id <= 0) {
    redirect(url('user/history.php'));
}

$sql = "SELECT b.*, p.status AS pay_status, p.amount, p.payment_method,
               t.ticket_code, t.status AS ticket_status,
               f.flight_number, f.departure_datetime, f.arrival_datetime, f.price,
               al.name AS airline_name, al.code AS airline_code,
               ao.city AS origin_city, ao.code AS origin_code, ao.name AS origin_name,
               ad.city AS dest_city, ad.code AS dest_code, ad.name AS dest_name
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        JOIN tickets t ON t.booking_id = b.id
        JOIN flights f ON f.id = b.flight_id
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE b.id = ? AND b.user_id = ? LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$id, $userId]);
$b = $st->fetch();
if (!$b) {
    flash('error', 'Data tidak ditemukan.');
    redirect(url('user/history.php'));
}

$stP = $pdo->prepare('SELECT full_name, id_number, date_of_birth FROM booking_passengers WHERE booking_id = ? ORDER BY id');
$stP->execute([$id]);
$passengers = $stP->fetchAll();

$pageTitle = 'Detail ' . $b['booking_code'] . ' — Rezky Maskapai';

$bookingStatusLabel = [
    'pending_payment' => 'Menunggu pembayaran',
    'awaiting_verification' => 'Menunggu verifikasi',
    'confirmed' => 'Dikonfirmasi',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
];
$bookingStatusBadge = [
    'pending_payment' => 'badge-pending',
    'awaiting_verification' => 'badge-await',
    'confirmed' => 'badge-ok',
    'rejected' => 'badge-bad',
    'cancelled' => 'badge-cancel',
];
$payStatusLabel = [
    'pending' => 'Belum dibayar',
    'awaiting_verification' => 'Menunggu verifikasi',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
];
$payStatusBadge = [
    'pending' => 'badge-pending',
    'awaiting_verification' => 'badge-await',
    'approved' => 'badge-ok',
    'rejected' => 'badge-bad',
];
$ticketStatusLabel = [
    'inactive' => 'Belum aktif',
    'active' => 'Aktif',
    'cancelled' => 'Dibatalkan',
];
$ticketStatusBadge = [
    'inactive' => 'badge-pending',
    'active' => 'badge-ok',
    'cancelled' => 'badge-cancel',
];

$bStatus = (string) $b['status'];
$paySt = (string) $b['pay_status'];
$tktSt = (string) $b['ticket_status'];

$depTs = strtotime($b['departure_datetime']);
$arrTs = strtotime($b['arrival_datetime']);
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
<h1 class="page-title">Detail pesanan</h1>

<article class="card booking-detail">
    <header class="booking-detail-header">
        <div class="booking-detail-code-row">
            <h2 class="booking-detail-code"><?= h($b['booking_code']) ?></h2>
            <span class="badge <?= h($bookingStatusBadge[$bStatus] ?? 'badge-cancel') ?>"><?= h($bookingStatusLabel[$bStatus] ?? $bStatus) ?></span>
        </div>
        <p class="booking-detail-created">
            Dibuat <?= h(date('d M Y, H:i', strtotime((string) $b['created_at']))) ?>
        </p>
        <div class="booking-detail-chips" role="list">
            <div class="booking-chip" role="listitem">
                <span class="booking-chip-label">Pembayaran</span>
                <span class="badge <?= h($payStatusBadge[$paySt] ?? 'badge-cancel') ?>"><?= h($payStatusLabel[$paySt] ?? $paySt) ?></span>
            </div>
            <div class="booking-chip" role="listitem">
                <span class="booking-chip-label">Tiket</span>
                <span class="badge <?= h($ticketStatusBadge[$tktSt] ?? 'badge-cancel') ?>"><?= h($ticketStatusLabel[$tktSt] ?? $tktSt) ?></span>
            </div>
        </div>
    </header>

    <section class="booking-detail-flight" aria-label="Rute penerbangan">
        <p class="booking-detail-airline"><?= h($b['airline_name']) ?> <?= h($b['flight_number']) ?></p>
        <p class="booking-detail-airline-meta">
            <span class="flight-detail-badge"><?= h($b['airline_code']) ?></span>
            <span class="muted">·</span>
            <span class="muted">Durasi perkiraan <?= h($durationLabel) ?></span>
        </p>
        <div class="flight-route flight-detail-route" role="group" aria-label="Bandara asal dan tujuan">
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Bandara asal</span>
                <strong class="flight-endpoint-place"><?= h($b['origin_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($b['origin_code']) ?>)</span>
            </div>
            <span class="flight-route-arrow" aria-hidden="true">→</span>
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Bandara tujuan</span>
                <strong class="flight-endpoint-place"><?= h($b['dest_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($b['dest_code']) ?>)</span>
            </div>
        </div>
        <p class="flight-detail-airport-names"><?= h($b['origin_name']) ?> → <?= h($b['dest_name']) ?></p>

        <div class="flight-detail-times">
            <div class="flight-time-card flight-time-card--depart">
                <span class="flight-time-card-label">Keberangkatan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', $depTs)) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', $depTs)) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', $depTs)) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($b['origin_name']) ?></span>
            </div>
            <div class="flight-time-card flight-time-card--arrive">
                <span class="flight-time-card-label">Kedatangan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', $arrTs)) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', $arrTs)) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', $arrTs)) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($b['dest_name']) ?></span>
            </div>
        </div>
    </section>

    <div class="flight-detail-stats booking-detail-totals">
        <div class="flight-stat">
            <span class="flight-stat-label">Total bayar</span>
            <span class="flight-stat-value flight-stat-price">Rp <?= h(number_format((float) $b['total_amount'], 0, ',', '.')) ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Jumlah kursi</span>
            <span class="flight-stat-value"><?= (int) $b['seat_count'] ?> kursi</span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Harga per kursi</span>
            <span class="flight-stat-value">Rp <?= h(number_format((float) $b['price'], 0, ',', '.')) ?></span>
        </div>
    </div>

    <dl class="booking-detail-extras">
        <div class="booking-extra">
            <dt>Metode pembayaran</dt>
            <dd><?= h(payment_method_label((string) ($b['payment_method'] ?? 'bank_transfer'))) ?></dd>
        </div>
        <div class="booking-extra">
            <dt>Kode tiket</dt>
            <dd><code class="booking-ticket-code"><?= h($b['ticket_code']) ?></code></dd>
        </div>
    </dl>
</article>

<section class="card booking-passengers" aria-labelledby="booking-passengers-heading">
    <h2 id="booking-passengers-heading" class="booking-passengers-title">Penumpang</h2>
    <ul class="booking-passenger-list">
        <?php foreach ($passengers as $i => $p): ?>
            <li class="booking-passenger-item">
                <span class="booking-passenger-num"><?= $i + 1 ?></span>
                <div class="booking-passenger-body">
                    <strong class="booking-passenger-name"><?= h($p['full_name']) ?></strong>
                    <span class="booking-passenger-id"><?= h($p['id_number']) ?></span>
                    <?php if (!empty($p['date_of_birth'])): ?>
                        <span class="booking-passenger-dob">Lahir <?= h(date('d M Y', strtotime((string) $p['date_of_birth']))) ?></span>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<div class="actions-inline no-print booking-detail-actions">
    <a class="btn btn-primary" href="<?= h(url('user/payment.php?booking_id=' . $id)) ?>">Pembayaran</a>
    <?php if ($b['status'] === 'confirmed'): ?>
        <a class="btn btn-secondary" href="<?= h(url('user/ticket_print.php?booking_id=' . $id)) ?>">E-ticket</a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= h(url('user/history.php')) ?>">Riwayat</a>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
