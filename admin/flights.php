<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Kelola Penerbangan — Rezky Maskapai';

$airlines = $pdo->query('SELECT id, name, code FROM airlines ORDER BY name')->fetchAll();
$airports = $pdo->query('SELECT id, code, city FROM airports ORDER BY city')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $airlineId = (int) ($_POST['airline_id'] ?? 0);
    $flightNumber = trim((string) ($_POST['flight_number'] ?? ''));
    $originId = (int) ($_POST['origin_airport_id'] ?? 0);
    $destId = (int) ($_POST['destination_airport_id'] ?? 0);
    $dep = trim((string) ($_POST['departure_datetime'] ?? ''));
    $arr = trim((string) ($_POST['arrival_datetime'] ?? ''));
    $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
    $cap = (int) ($_POST['seat_capacity'] ?? 0);
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    if ($airlineId < 1 || $originId < 1 || $destId < 1 || $originId === $destId
        || strlen($flightNumber) < 2 || $cap < 1 || $price <= 0) {
        flash('error', 'Data penerbangan tidak valid.');
        redirect(url('admin/flights.php'));
    }
    $depSql = str_replace('T', ' ', $dep);
    $arrSql = str_replace('T', ' ', $arr);
    if (strlen($depSql) === 16) {
        $depSql .= ':00';
    }
    if (strlen($arrSql) === 16) {
        $arrSql .= ':00';
    }
    if ($id > 0) {
        $pdo->prepare(
            'UPDATE flights SET airline_id=?, flight_number=?, origin_airport_id=?, destination_airport_id=?,
             departure_datetime=?, arrival_datetime=?, price=?, seat_capacity=?, status=? WHERE id=?'
        )->execute([$airlineId, $flightNumber, $originId, $destId, $depSql, $arrSql, $price, $cap, $status, $id]);
        flash('success', 'Penerbangan diperbarui.');
    } else {
        $pdo->prepare(
            'INSERT INTO flights (airline_id, flight_number, origin_airport_id, destination_airport_id,
             departure_datetime, arrival_datetime, price, seat_capacity, status)
             VALUES (?,?,?,?,?,?,?,?,?)'
        )->execute([$airlineId, $flightNumber, $originId, $destId, $depSql, $arrSql, $price, $cap, $status]);
        flash('success', 'Penerbangan ditambahkan.');
    }
    redirect(url('admin/flights.php'));
}

if (isset($_GET['delete'])) {
    $did = (int) $_GET['delete'];
    if ($did > 0) {
        try {
            $pdo->prepare('DELETE FROM flights WHERE id = ?')->execute([$did]);
            flash('success', 'Penerbangan dihapus.');
        } catch (Throwable $e) {
            flash('error', 'Tidak dapat menghapus: sudah ada pemesanan terkait.');
        }
    }
    redirect(url('admin/flights.php'));
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch();
}

$sql = "SELECT f.*, al.name AS airline_name, ao.code AS ocode, ad.code AS dcode
        FROM flights f
        JOIN airlines al ON al.id = f.airline_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        ORDER BY f.departure_datetime DESC";
$rows = $pdo->query($sql)->fetchAll();

function dt_local(?string $sqlDt): string
{
    if (!$sqlDt) {
        return '';
    }
    $t = strtotime($sqlDt);
    return date('Y-m-d\TH:i', $t);
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title" style="font-size: 2rem; font-weight: 800;">Kelola Penerbangan</h1>

<div class="card" style="border: 2px solid var(--accent);">
    <h2 style="margin-top:0; color: var(--primary); font-size: 1.5rem; font-weight: 700;"><?= $edit ? 'Ubah jadwal penerbangan' : 'Tambah penerbangan baru' ?></h2>
    <p class="muted" style="margin-bottom: 1.5rem;"><?= $edit ? 'Edit informasi penerbangan yang sudah ada.' : 'Tambah jadwal penerbangan baru ke sistem.' ?></p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
        <div class="form-row">
            <label><span style="font-weight: 700;">Maskapai</span>
                <select name="airline_id" required style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                    <?php foreach ($airlines as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= ($edit && (int)$edit['airline_id'] === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= h($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><span style="font-weight: 700;">No. penerbangan</span><input name="flight_number" required maxlength="20" value="<?= h($edit['flight_number'] ?? '') ?>" placeholder="GA-123" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;"></label>
        </div>
        <div class="form-row">
            <label><span style="font-weight: 700;">Asal</span>
                <select name="origin_airport_id" required style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                    <?php foreach ($airports as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= ($edit && (int)$edit['origin_airport_id'] === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= h($a['city'] . ' (' . $a['code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><span style="font-weight: 700;">Tujuan</span>
                <select name="destination_airport_id" required style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                    <?php foreach ($airports as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= ($edit && (int)$edit['destination_airport_id'] === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= h($a['city'] . ' (' . $a['code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="form-row">
            <label><span style="font-weight: 700;">Berangkat</span><input type="datetime-local" name="departure_datetime" required value="<?= h(dt_local($edit['departure_datetime'] ?? null)) ?>" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;"></label>
            <label><span style="font-weight: 700;">Tiba</span><input type="datetime-local" name="arrival_datetime" required value="<?= h(dt_local($edit['arrival_datetime'] ?? null)) ?>" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;"></label>
        </div>
        <div class="form-row">
            <label><span style="font-weight: 700;">Harga per kursi (Rp)</span><input type="number" name="price" min="1" step="1" required value="<?= h((string)($edit['price'] ?? '500000')) ?>" placeholder="500000" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;"></label>
            <label><span style="font-weight: 700;">Kapasitas kursi</span><input type="number" name="seat_capacity" min="1" max="500" required value="<?= h((string)($edit['seat_capacity'] ?? '180')) ?>" placeholder="180" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;"></label>
        </div>
        <label><span style="font-weight: 700;">Status</span>
            <select name="status" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                <option value="active" <?= (!$edit || $edit['status'] === 'active') ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($edit && $edit['status'] === 'inactive') ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </label>
        <div class="actions-inline">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 8px;"><?= $edit ? 'Simpan perubahan' : 'Tambah penerbangan' ?></button>
            <?php if ($edit): ?>
                <a class="btn btn-ghost" href="<?= h(url('admin/flights.php')) ?>">Batal edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<h2 style="color: var(--primary); font-size: 1.5rem; font-weight: 700; margin: 2rem 0 1rem;">Daftar Penerbangan</h2>

<div class="grid-flights">
    <?php foreach ($rows as $r): ?>
        <?php $av = flight_available_seats($pdo, (int) $r['id']); ?>
        <article class="card flight-row">
            <div class="flight-row-main">
                <p class="flight-airline-line">
                    <strong><?= h($r['airline_name']) ?></strong>
                    <span class="flight-detail-badge"><?= h($r['flight_number']) ?></span>
                </p>
                <div class="flight-route">
                    <div class="flight-endpoint">
                        <strong><?= h($r['ocode']) ?></strong>
                    </div>
                    <span class="flight-route-arrow">→</span>
                    <div class="flight-endpoint">
                        <strong><?= h($r['dcode']) ?></strong>
                    </div>
                </div>
                <p class="muted" style="margin: 0.5rem 0;"><?= h(date('d M Y H:i', strtotime($r['departure_datetime']))) ?></p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;">Rp <?= h(number_format((float) $r['price'], 0, ',', '.')) ?></span>
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= (int) $r['seat_capacity'] ?> kursi</span>
                    <span class="badge" style="background: <?= $r['status'] === 'active' ? '#dcfce7' : '#f1f5f9' ?>; color: <?= $r['status'] === 'active' ? '#166534' : 'var(--muted)' ?>; font-weight: 600;"><?= h($r['status'] === 'active' ? 'Aktif' : 'Nonaktif') ?></span>
                    <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 600;"><?= $av ?> tersedia</span>
                </div>
            </div>
            <div class="actions-inline">
                <a class="btn btn-sm btn-ghost" href="<?= h(url('admin/flights.php?edit=' . (int) $r['id'])) ?>" style="font-weight: 600;">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url('admin/flights.php?delete=' . (int) $r['id'])) ?>" onclick="return confirm('Hapus penerbangan ini?');" style="font-weight: 600;">Hapus</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
