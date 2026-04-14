<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$bookingId = (int) ($_GET['booking_id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];

if ($bookingId <= 0) {
    redirect(url('user/history.php'));
}

$sql = "SELECT b.*, p.id AS payment_id, p.amount, p.payment_method, p.status AS pay_status, p.admin_note,
               f.flight_number, f.departure_datetime, f.arrival_datetime, f.price,
               al.name AS airline_name,
               ao.city AS origin_city, ao.code AS origin_code,
               ad.city AS dest_city, ad.code AS dest_code
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        JOIN flights f ON f.id = b.flight_id
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE b.id = ? AND b.user_id = ? LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$bookingId, $userId]);
$row = $st->fetch();
if (!$row) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect(url('user/history.php'));
}

$stPr = $pdo->prepare('SELECT * FROM payment_proofs WHERE payment_id = ? ORDER BY id DESC LIMIT 1');
$stPr->execute([(int) $row['payment_id']]);
$lastProof = $stPr->fetch();

$pageTitle = 'Pembayaran ' . $row['booking_code'] . ' — Rezky Maskapai';

$statusLabel = [
    'pending_payment' => ['Menunggu pembayaran', 'badge-pending'],
    'awaiting_verification' => ['Menunggu verifikasi admin', 'badge-await'],
    'confirmed' => ['Lunas — tiket aktif', 'badge-ok'],
    'rejected' => ['Ditolak', 'badge-bad'],
    'cancelled' => ['Dibatalkan', 'badge-cancel'],
];

$payMap = [
    'pending' => ['Belum ada bukti', 'badge-pending'],
    'awaiting_verification' => ['Bukti diunggah — tunggu verifikasi', 'badge-await'],
    'approved' => ['Pembayaran disetujui', 'badge-ok'],
    'rejected' => ['Pembayaran ditolak', 'badge-bad'],
];

[$bs, $bc] = $statusLabel[$row['status']] ?? [$row['status'], 'badge-pending'];
[$ps, $pc] = $payMap[$row['pay_status']] ?? [$row['pay_status'], 'badge-pending'];

$pmCode = (string) ($row['payment_method'] ?? 'bank_transfer');
$pmLabel = payment_method_label($pmCode);
$amountFmt = 'Rp ' . number_format((float) $row['total_amount'], 0, ',', '.');
$instrLines = payment_method_instructions($pmCode, $row['booking_code'], $amountFmt);

$canUpload = in_array($row['status'], ['pending_payment', 'awaiting_verification'], true)
    && in_array($row['pay_status'], ['pending', 'awaiting_verification'], true)
    && $row['status'] !== 'rejected';

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Invoice &amp; pembayaran</h1>

<article class="card booking-detail">
    <header class="booking-detail-header">
        <div class="booking-detail-code-row">
            <h2 class="booking-detail-code"><?= h($row['booking_code']) ?></h2>
            <span class="badge <?= h($bc) ?>"><?= h($bs) ?></span>
        </div>
        <div class="booking-detail-chips" role="list">
            <div class="booking-chip" role="listitem">
                <span class="booking-chip-label">Pembayaran</span>
                <span class="badge <?= h($pc) ?>"><?= h($ps) ?></span>
            </div>
            <div class="booking-chip" role="listitem">
                <span class="booking-chip-label">Metode</span>
                <span class="muted"><?= h($pmLabel) ?></span>
            </div>
        </div>
    </header>

    <section class="booking-detail-flight" aria-label="Rute penerbangan">
        <p class="booking-detail-airline"><?= h($row['airline_name']) ?> <?= h($row['flight_number']) ?></p>
        <div class="flight-route flight-detail-route" role="group" aria-label="Bandara asal dan tujuan">
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Dari</span>
                <strong class="flight-endpoint-place"><?= h($row['origin_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($row['origin_code']) ?>)</span>
            </div>
            <span class="flight-route-arrow" aria-hidden="true">→</span>
            <div class="flight-endpoint">
                <span class="flight-endpoint-label">Ke</span>
                <strong class="flight-endpoint-place"><?= h($row['dest_city']) ?></strong>
                <span class="flight-endpoint-code">(<?= h($row['dest_code']) ?>)</span>
            </div>
        </div>

        <div class="flight-detail-times">
            <div class="flight-time-card flight-time-card--depart">
                <span class="flight-time-card-label">Keberangkatan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', strtotime($row['departure_datetime']))) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', strtotime($row['departure_datetime']))) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', strtotime($row['departure_datetime']))) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($row['origin_city']) ?></span>
            </div>
            <div class="flight-time-card flight-time-card--arrive">
                <span class="flight-time-card-label">Kedatangan</span>
                <time class="flight-time-card-datetime" datetime="<?= h(date('c', strtotime($row['arrival_datetime']))) ?>">
                    <span class="flight-time-card-date"><?= h(date('d M Y', strtotime($row['arrival_datetime']))) ?></span>
                    <span class="flight-time-card-clock"><?= h(date('H:i', strtotime($row['arrival_datetime']))) ?></span>
                </time>
                <span class="flight-time-card-place"><?= h($row['dest_city']) ?></span>
            </div>
        </div>
    </section>

    <div class="flight-detail-stats booking-detail-totals">
        <div class="flight-stat">
            <span class="flight-stat-label">Total tagihan</span>
            <span class="flight-stat-value flight-stat-price"><?= h($amountFmt) ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Jumlah kursi</span>
            <span class="flight-stat-value"><?= (int) $row['seat_count'] ?> kursi</span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Harga per kursi</span>
            <span class="flight-stat-value">Rp <?= h(number_format((float) $row['price'], 0, ',', '.')) ?></span>
        </div>
    </div>

    <?php if (!empty($row['admin_note'])): ?>
        <div class="alert alert-info">
            <strong>Catatan admin:</strong> <?= h($row['admin_note']) ?>
        </div>
    <?php endif; ?>
</article>

<div class="card">
    <h3 style="margin-top:0; color: var(--primary);">Instruksi pembayaran</h3>
    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 10px; border: 1px solid rgba(148, 163, 184, 0.3);">
        <ul class="muted" style="margin:0;padding-left:1.25rem;line-height:1.7;">
            <?php foreach ($instrLines as $line): ?>
                <li><?= h($line) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php if ($lastProof): ?>
    <div class="card">
        <h3 style="margin-top:0; color: var(--primary);">Bukti pembayaran terakhir</h3>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-top: 0.5rem;">
            <div>
                <p class="muted" style="margin:0;">
                    <strong>Nama file:</strong> <?= h($lastProof['original_filename']) ?><br>
                    <strong>Diunggah:</strong> <?= h($lastProof['created_at']) ?>
                </p>
            </div>
            <a class="btn btn-outline" target="_blank" href="<?= h(asset('uploads/proofs/' . $lastProof['stored_filename'])) ?>">
                Lihat file
            </a>
        </div>
    </div>
<?php endif; ?>

<?php if ($row['status'] === 'confirmed'): ?>
    <div class="actions-inline no-print" style="margin-top: 1.5rem;">
        <a class="btn btn-primary" href="<?= h(url('user/ticket_print.php?booking_id=' . $bookingId)) ?>">Cetak e-ticket</a>
        <a class="btn btn-secondary" href="<?= h(url('user/history.php')) ?>">Kembali ke riwayat</a>
    </div>
<?php endif; ?>

<?php if ($canUpload): ?>
    <div class="card" style="border: 2px solid var(--accent);">
        <h3 style="margin-top:0; color: var(--primary);">Unggah bukti transfer</h3>
        <p class="muted">Format JPG, PNG, atau PDF. Maksimal 2 MB.</p>
        <form method="post" action="<?= h(url('user/payment_upload.php')) ?>" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_id" value="<?= (int) $bookingId ?>">
            <label>
                <span>File bukti</span>
                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required>
            </label>
            <button type="submit" class="btn btn-primary">Kirim bukti</button>
        </form>
    </div>
<?php endif; ?>

<div class="no-print actions-inline" style="margin-top:1.5rem;">
    <a class="btn btn-ghost" href="<?= h(url('user/booking_detail.php?id=' . $bookingId)) ?>">Detail pesanan</a>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
