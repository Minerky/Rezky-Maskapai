<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$flightId = (int) ($_GET['flight_id'] ?? 0);
if ($flightId <= 0) {
    redirect(url('user/index.php'));
}

$sql = "SELECT f.*, al.name AS airline_name,
               ao.city AS origin_city, ao.code AS origin_code,
               ad.city AS dest_city, ad.code AS dest_code
        FROM flights f
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        WHERE f.id = ? AND f.status = 'active' LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$flightId]);
$flight = $st->fetch();
if (!$flight) {
    flash('error', 'Penerbangan tidak tersedia.');
    redirect(url('user/index.php'));
}

$avail = flight_available_seats($pdo, $flightId);
$pageTitle = 'Data Pemesanan — Rezky Maskapai';

$maxBook = min(8, max(1, $avail));

// Get user data for auto-fill from database
$userId = (int) ($_SESSION['user']['id'] ?? 0);
$stmt = $pdo->prepare('SELECT full_name, nik FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$userData = $stmt->fetch() ?: ['full_name' => '', 'nik' => ''];
$userName = $userData['full_name'] ?? '';
$userNik = $userData['nik'] ?? '';

// Check if booking for self is selected (default true)
$bookForSelf = isset($_POST['book_for_self']) ? (bool) $_POST['book_for_self'] : true;

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card flight-detail">
    <div class="flight-detail-header">
        <p class="flight-detail-airline"><?= h($flight['airline_name']) ?> <?= h($flight['flight_number']) ?></p>
        <p class="flight-detail-meta-line">
            <span class="flight-detail-badge"><?= h($flight['origin_code']) ?></span>
            <span class="muted">·</span>
            <span class="muted"><?= (string) $avail ?> kursi tersedia</span>
        </p>
    </div>

    <div class="flight-route flight-detail-route" role="group" aria-label="Bandara asal dan tujuan">
        <div class="flight-endpoint">
            <span class="flight-endpoint-label">Dari</span>
            <strong class="flight-endpoint-place"><?= h($flight['origin_city']) ?></strong>
            <span class="flight-endpoint-code">(<?= h($flight['origin_code']) ?>)</span>
        </div>
        <span class="flight-route-arrow" aria-hidden="true">→</span>
        <div class="flight-endpoint">
            <span class="flight-endpoint-label">Ke</span>
            <strong class="flight-endpoint-place"><?= h($flight['dest_city']) ?></strong>
            <span class="flight-endpoint-code">(<?= h($flight['dest_code']) ?>)</span>
        </div>
    </div>

    <div class="flight-depart-block">
        <span class="flight-depart-label">Keberangkatan</span>
        <div class="flight-depart-datetime">
            <span class="flight-depart-date"><?= h(date('d M Y', strtotime($flight['departure_datetime']))) ?></span>
            <span class="flight-depart-time"><?= h(date('H:i', strtotime($flight['departure_datetime']))) ?></span>
        </div>
    </div>

    <div class="flight-detail-stats">
        <div class="flight-stat">
            <span class="flight-stat-label">Harga per kursi</span>
            <span class="flight-stat-value flight-stat-price">Rp <?= h(number_format((float) $flight['price'], 0, ',', '.')) ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Kursi tersedia</span>
            <span class="flight-stat-value"><?= $avail ?></span>
        </div>
        <div class="flight-stat">
            <span class="flight-stat-label">Maskapai</span>
            <span class="flight-stat-value"><?= h($flight['airline_name']) ?></span>
        </div>
    </div>
</div>

<?php if ($avail < 1): ?>
    <div class="alert alert-error">Maaf, kursi untuk penerbangan ini sudah habis.</div>
<?php else: ?>
    <div class="card">
        <h2 style="margin-top:0;">Data penumpang</h2>
        <form method="post" action="<?= h(url('user/booking_store.php')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="flight_id" value="<?= (int) $flightId ?>">
            <label>
                <span>Jumlah kursi (maks <?= (int) $maxBook ?>)</span>
                <select name="seat_count" data-seat-count required>
                    <?php for ($i = 1; $i <= $maxBook; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> penumpang</option>
                    <?php endfor; ?>
                </select>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="book_for_self" id="book_for_self" value="1" checked>
                <span>Pesan untuk diri sendiri (auto-fill data akun)</span>
            </label>
            <fieldset class="payment-method-fieldset">
                <legend><strong>Metode pembayaran</strong></legend>
                <p class="muted" style="margin:0 0 0.75rem;">Pilih cara bayar untuk tagihan ini. Instruksi rinci muncul di halaman pembayaran.</p>
                <div class="payment-options">
                    <?php foreach (payment_method_labels() as $code => $plabel): ?>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="<?= h($code) ?>" <?= $code === 'bank_transfer' ? 'checked' : '' ?> required>
                            <span><?= h($plabel) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <div data-passenger-rows>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <div class="passenger-row passenger-block" data-row="<?= $i ?>">
                        <strong>Penumpang <?= $i ?></strong>
                        <div class="form-row" style="margin-top:0.5rem;">
                            <label>
                                <span>Nama lengkap</span>
                                <input type="text" name="p_name[]" maxlength="120" <?= $i === 1 ? 'required' : '' ?> value="<?= ($i === 1 && $bookForSelf) ? h($userName) : '' ?>">
                            </label>
                            <label>
                                <span>No. identitas (KTP/Paspor)</span>
                                <input type="text" name="p_id[]" maxlength="50" <?= $i === 1 ? 'required' : '' ?> value="<?= ($i === 1 && $bookForSelf) ? h($userNik) : '' ?>">
                            </label>
                        </div>
                        <label>
                            <span>Tanggal lahir</span>
                            <input type="date" name="p_dob[]">
                        </label>
                    </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary">Buat pesanan &amp; invoice</button>
        </form>
    </div>
<?php endif; ?>

<script>
(function() {
    var checkbox = document.getElementById('book_for_self');
    var firstNameInput = document.querySelector('input[name="p_name[]"]');
    var firstIdInput = document.querySelector('input[name="p_id[]"]');
    var userName = "<?= h($userName) ?>";
    var userNik = "<?= h($userNik) ?>";

    function updatePassengerFields() {
        if (checkbox.checked) {
            firstNameInput.value = userName;
            firstIdInput.value = userNik;
        } else {
            firstNameInput.value = '';
            firstIdInput.value = '';
        }
    }

    if (checkbox && firstNameInput && firstIdInput) {
        // Auto-fill saat halaman dimuat
        updatePassengerFields();

        // Update saat checkbox berubah
        checkbox.addEventListener('change', updatePassengerFields);
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
