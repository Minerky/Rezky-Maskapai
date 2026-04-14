<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(url('user/index.php'));
}

$sql = "SELECT f.*, al.name AS airline_name, al.code AS airline_code,
               ao.city AS origin_city, ao.code AS origin_code, ao.name AS origin_name,
               ad.city AS dest_city, ad.code AS dest_code, ad.name AS dest_name
        FROM flights f
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE f.id = ? AND f.status = 'active' LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$id]);
$flight = $st->fetch();
if (!$flight) {
    flash('error', 'Penerbangan tidak ditemukan.');
    redirect(url('user/index.php'));
}

$avail = flight_available_seats($pdo, $id);
$pageTitle = 'Detail ' . $flight['flight_number'] . ' — Rezky Maskapai';

$depTs = strtotime($flight['departure_datetime']);
$arrTs = strtotime($flight['arrival_datetime']);
$durationMin = max(0, (int) round(($arrTs - $depTs) / 60));
$durH = intdiv($durationMin, 60);
$durM = $durationMin % 60;
if ($durH > 0) {
    $durationLabel = $durM > 0 ? $durH . ' j ' . $durM . ' m' : $durH . ' jam';
} else {
    $durationLabel = $durM . ' menit';
}

$qs = 'flight_id=' . $id;

require_once __DIR__ . '/../includes/header.php';
?>
<article class="card flight-detail">
    <header class="flight-detail-header">
        <p class="flight-detail-airline"><?= h($flight['airline_name']) ?> <?= h($flight['flight_number']) ?></p>
        <p class="flight-detail-meta-line">
            <span class="flight-detail-badge"><?= h($flight['airline_code']) ?></span>
            <span class="muted">·</span>
            <span class="muted">Durasi perkiraan <?= h($durationLabel) ?></span>
        </p>
    </header>

    <div class="flight-route flight-detail-route" role="group" aria-label="Bandara asal dan tujuan">
        <div class="flight-endpoint">
            <span class="flight-endpoint-label">Bandara asal</span>
            <strong class="flight-endpoint-place"><?= h($flight['origin_city']) ?></strong>
            <span class="flight-endpoint-code">(<?= h($flight['origin_code']) ?>)</span>
        </div>
        <span class="flight-route-arrow" aria-hidden="true">→</span>
        <div class="flight-endpoint">
            <span class="flight-endpoint-label">Bandara tujuan</span>
            <strong class="flight-endpoint-place"><?= h($flight['dest_city']) ?></strong>
            <span class="flight-endpoint-code">(<?= h($flight['dest_code']) ?>)</span>
        </div>
    </div>
    <p class="flight-detail-airport-names"><?= h($flight['origin_name']) ?> → <?= h($flight['dest_name']) ?></p>

    <div class="flight-detail-times">
        <div class="flight-time-card flight-time-card--depart">
            <span class="flight-time-card-label">Keberangkatan</span>
            <time class="flight-time-card-datetime" datetime="<?= h(date('c', $depTs)) ?>">
                <span class="flight-time-card-date"><?= h(date('d M Y', $depTs)) ?></span>
                <span class="flight-time-card-clock"><?= h(date('H:i', $depTs)) ?></span>
            </time>
            <span class="flight-time-card-place"><?= h($flight['origin_name']) ?></span>
        </div>
        <div class="flight-time-card flight-time-card--arrive">
            <span class="flight-time-card-label">Kedatangan</span>
            <time class="flight-time-card-datetime" datetime="<?= h(date('c', $arrTs)) ?>">
                <span class="flight-time-card-date"><?= h(date('d M Y', $arrTs)) ?></span>
                <span class="flight-time-card-clock"><?= h(date('H:i', $arrTs)) ?></span>
            </time>
            <span class="flight-time-card-place"><?= h($flight['dest_name']) ?></span>
        </div>
    </div>

    <div class="flight-detail-stats">
        <div class="flight-stat">
            <span class="flight-stat-label">Harga per kursi</span>
            <span class="flight-stat-value flight-stat-price">Rp <?= h(number_format((float) $flight['price'], 0, ',', '.')) ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Kapasitas kabin</span>
            <span class="flight-stat-value"><?= (int) $flight['seat_capacity'] ?> kursi</span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Tersedia saat ini</span>
            <span class="flight-stat-value"><?= (int) $avail ?> kursi</span>
        </div>
    </div>

    <div class="actions-inline no-print flight-detail-actions">
        <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['role_slug'] ?? '') === 'user'): ?>
            <?php if ($avail > 0): ?>
                <a class="btn btn-primary" href="<?= h(url('user/booking.php?' . $qs)) ?>">Lanjut Pesan</a>
            <?php else: ?>
                <span class="badge badge-bad">Penuh</span>
            <?php endif; ?>
        <?php elseif (!empty($_SESSION['user']) && ($_SESSION['user']['role_slug'] ?? '') === 'admin'): ?>
            <p class="muted">Masuk sebagai penumpang untuk memesan tiket.</p>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= h(url('auth/login.php')) ?>">Masuk untuk Pesan</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="<?= h(url('user/index.php')) ?>">Kembali ke jadwal</a>
    </div>
</article>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
