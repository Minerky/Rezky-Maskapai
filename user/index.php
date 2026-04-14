<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$pageTitle = 'Jadwal Penerbangan — Rezky Maskapai';

$airports = $pdo->query('SELECT id, code, city, name FROM airports ORDER BY city ASC, code ASC')->fetchAll();

$originId = (int) ($_GET['origin'] ?? 0);
$destId = (int) ($_GET['dest'] ?? 0);
$dateRaw = isset($_GET['date']) ? trim((string) $_GET['date']) : '';
$departureDate = '';
if ($dateRaw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateRaw)) {
    $departureDate = $dateRaw;
}

$where = ["f.status = 'active'", 'f.departure_datetime >= NOW()'];
$params = [];

if ($originId > 0) {
    $where[] = 'f.origin_airport_id = ?';
    $params[] = $originId;
}
if ($destId > 0) {
    $where[] = 'f.destination_airport_id = ?';
    $params[] = $destId;
}
if ($departureDate !== '') {
    $where[] = 'DATE(f.departure_datetime) = ?';
    $params[] = $departureDate;
}

$sql = "SELECT f.id, f.flight_number, f.departure_datetime, f.arrival_datetime, f.price, f.seat_capacity,
               al.name AS airline_name, al.code AS airline_code,
               ao.city AS origin_city, ao.code AS origin_code,
               ad.city AS dest_city, ad.code AS dest_code
        FROM flights f
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE " . implode(' AND ', $where) . '
        ORDER BY f.departure_datetime ASC';

$st = $pdo->prepare($sql);
$st->execute($params);
$flights = $st->fetchAll();
foreach ($flights as &$f) {
    $f['available'] = flight_available_seats($pdo, (int) $f['id']);
}
unset($f);

require_once __DIR__ . '/../includes/header.php';
?>
<section class="hero">
    <h1>Jadwal penerbangan</h1>
    <p>Temukan penerbangan terbaik untuk perjalanan Anda - harga transparan, proses mudah.</p>

    <form class="search-panel" method="get" action="<?= h(url('user/index.php')) ?>">
        <div class="search-grid">
            <label>
                <span>Bandara asal</span>
                <select name="origin">
                    <option value="">Semua bandara</option>
                    <?php foreach ($airports as $ap): ?>
                        <option value="<?= (int) $ap['id'] ?>" <?= $originId === (int) $ap['id'] ? 'selected' : '' ?>>
                            <?= h($ap['city']) ?> (<?= h($ap['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Bandara tujuan</span>
                <select name="dest">
                    <option value="">Semua bandara</option>
                    <?php foreach ($airports as $ap): ?>
                        <option value="<?= (int) $ap['id'] ?>" <?= $destId === (int) $ap['id'] ? 'selected' : '' ?>>
                            <?= h($ap['city']) ?> (<?= h($ap['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Tanggal berangkat</span>
                <input type="date" name="date" value="<?= h($departureDate) ?>">
            </label>
            <div class="search-actions">
                <button type="submit" class="btn btn-primary">Cari penerbangan</button>
                <a class="btn btn-ghost" href="<?= h(url('user/index.php')) ?>">Reset</a>
            </div>
        </div>
    </form>
</section>

<?php if (!$flights): ?>
    <div class="card" style="text-align: center; padding: 3rem 2rem;">
        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Tidak ada penerbangan ditemukan</h3>
        <p class="muted">
            <?php if ($originId || $destId || $departureDate !== ''): ?>
                Tidak ada keberangkatan yang cocok dengan pencarian Anda. Ubah filter atau <a href="<?= h(url('user/index.php')) ?>">reset pencarian</a>.
            <?php else: ?>
                Belum ada penerbangan mendatang. Silakan cek lagi nanti atau hubungi administrator.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="grid-flights">
        <?php foreach ($flights as $f): ?>
            <?php
            $depTs = strtotime($f['departure_datetime']);
            $arrTs = strtotime($f['arrival_datetime']);
            ?>
            <article class="card flight-row">
                <div class="flight-row-main">
                    <p class="flight-airline-line">
                        <strong><?= h($f['airline_name']) ?></strong>
                        <span class="flight-detail-badge"><?= h($f['airline_code']) ?></span>
                        <span class="muted">·</span>
                        <span class="muted"><?= h($f['flight_number']) ?></span>
                    </p>
                    <div class="flight-route" role="group" aria-label="Bandara asal dan tujuan">
                        <div class="flight-endpoint">
                            <span class="flight-endpoint-label">Dari</span>
                            <strong class="flight-endpoint-place"><?= h($f['origin_city']) ?></strong>
                            <span class="flight-endpoint-code">(<?= h($f['origin_code']) ?>)</span>
                        </div>
                        <span class="flight-route-arrow" aria-hidden="true">→</span>
                        <div class="flight-endpoint">
                            <span class="flight-endpoint-label">Ke</span>
                            <strong class="flight-endpoint-place"><?= h($f['dest_city']) ?></strong>
                            <span class="flight-endpoint-code">(<?= h($f['dest_code']) ?>)</span>
                        </div>
                    </div>
                    <div class="flight-depart-block">
                        <span class="flight-depart-label">Keberangkatan</span>
                        <time class="flight-depart-datetime" datetime="<?= h(date('c', $depTs)) ?>">
                            <span class="flight-depart-date"><?= h(date('d M Y', $depTs)) ?></span>
                            <span class="flight-depart-time"><?= h(date('H:i', $depTs)) ?></span>
                        </time>
                        <p class="flight-meta-secondary">
                            Kedatangan <?= h(date('H:i', $arrTs)) ?>
                            · <strong><?= (int) $f['available'] ?></strong> kursi tersedia
                        </p>
                    </div>
                </div>
                <div class="flight-meta">
                    <div class="price-tag">Rp <?= h(number_format((float) $f['price'], 0, ',', '.')) ?></div>
                    <div class="muted" style="font-size: 0.8rem; margin-top: 0.25rem;">per kursi</div>
                </div>
                <div>
                    <a class="btn btn-primary" href="<?= h(url('user/flight_detail.php?id=' . (int) $f['id'])) ?>">Pesan</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card" style="margin-top:2rem;">
    <h2 style="color: var(--primary);">Cara memesan tiket</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1rem;">
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 800; font-size: 1.25rem; color: var(--accent); margin-bottom: 0.5rem;">1</div>
            <strong>Pilih penerbangan</strong>
            <p class="muted" style="font-size: 0.9rem;">Cari dan pilih jadwal penerbangan yang sesuai</p>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 800; font-size: 1.25rem; color: var(--accent); margin-bottom: 0.5rem;">2</div>
            <strong>Isi data penumpang</strong>
            <p class="muted" style="font-size: 0.9rem;">Lengkapi informasi penumpang dan metode pembayaran</p>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 800; font-size: 1.25rem; color: var(--accent); margin-bottom: 0.5rem;">3</div>
            <strong>Selesaikan pembayaran</strong>
            <p class="muted" style="font-size: 0.9rem;">Unggah bukti transfer dan tunggu verifikasi</p>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <div style="font-weight: 800; font-size: 1.25rem; color: var(--accent); margin-bottom: 0.5rem;">4</div>
            <strong>Cetak e-ticket</strong>
            <p class="muted" style="font-size: 0.9rem;">Setelah disetujui, cetak tiket elektronik Anda</p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
