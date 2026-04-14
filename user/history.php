<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$pageTitle = 'Riwayat Transaksi — Rezky Maskapai';
$userId = (int) $_SESSION['user']['id'];

$sql = "SELECT b.id, b.booking_code, b.seat_count, b.total_amount, b.status, b.created_at,
               p.status AS pay_status, p.payment_method,
               f.flight_number, f.departure_datetime,
               ao.city AS origin_city, ao.code AS origin_code,
               ad.city AS dest_city, ad.code AS dest_code
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        JOIN flights f ON f.id = b.flight_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC";
$st = $pdo->prepare($sql);
$st->execute([$userId]);
$rows = $st->fetchAll();

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

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Riwayat pemesanan</h1>

<?php if (!$rows): ?>
    <div class="card">
        <p class="muted" style="text-align:center; padding: 2rem 0;">Belum ada transaksi.</p>
    </div>
<?php else: ?>
    <div class="grid-flights">
        <?php foreach ($rows as $r): ?>
            <?php
            $bStatus = (string) $r['status'];
            $paySt = (string) $r['pay_status'];
            ?>
            <article class="card flight-row">
                <div class="flight-row-main">
                    <div class="flight-airline-line">
                        <strong><?= h($r['booking_code']) ?></strong>
                        <span class="muted">·</span>
                        <span class="muted"><?= h($r['flight_number']) ?></span>
                    </div>
                    <div class="flight-route">
                        <div class="flight-endpoint">
                            <span class="flight-endpoint-place"><?= h($r['origin_city']) ?></span>
                            <span class="flight-endpoint-code">(<?= h($r['origin_code']) ?>)</span>
                        </div>
                        <span class="flight-route-arrow">→</span>
                        <div class="flight-endpoint">
                            <span class="flight-endpoint-place"><?= h($r['dest_city']) ?></span>
                            <span class="flight-endpoint-code">(<?= h($r['dest_code']) ?>)</span>
                        </div>
                    </div>
                    <div class="flight-depart-block">
                        <span class="flight-depart-label">Keberangkatan</span>
                        <div class="flight-depart-datetime">
                            <span class="flight-depart-date"><?= h(date('d M Y', strtotime($r['departure_datetime']))) ?></span>
                            <span class="flight-depart-time"><?= h(date('H:i', strtotime($r['departure_datetime']))) ?></span>
                        </div>
                    </div>
                    <div class="flight-meta-secondary">
                        <span class="badge <?= h($bookingStatusBadge[$bStatus] ?? 'badge-cancel') ?>"><?= h($bookingStatusLabel[$bStatus] ?? $bStatus) ?></span>
                        <span class="muted">·</span>
                        <span class="badge <?= h($payStatusBadge[$paySt] ?? 'badge-cancel') ?>"><?= h($payStatusLabel[$paySt] ?? $paySt) ?></span>
                        <span class="muted">·</span>
                        <span class="muted"><?= h(payment_method_label((string) ($r['payment_method'] ?? 'bank_transfer'))) ?></span>
                    </div>
                </div>
                <div class="flight-meta">
                    <div class="price-tag">Rp <?= h(number_format((float) $r['total_amount'], 0, ',', '.')) ?></div>
                    <div class="muted" style="font-size: 0.8rem; margin-top: 0.25rem;"><?= (int) $r['seat_count'] ?> kursi</div>
                </div>
                <div>
                    <a class="btn btn-primary btn-sm" href="<?= h(url('user/booking_detail.php?id=' . (int) $r['id'])) ?>">Detail</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
