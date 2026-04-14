<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Kelola Bandara — Rezky Maskapai';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $action = (string) ($_POST['form_action'] ?? '');
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $country = trim((string) ($_POST['country'] ?? 'Indonesia'));
        if (strlen($code) < 3 || strlen($name) < 2 || strlen($city) < 2) {
            flash('error', 'Data bandara tidak valid.');
        } else {
            if ($id > 0) {
                $pdo->prepare('UPDATE airports SET code=?, name=?, city=?, country=? WHERE id=?')
                    ->execute([$code, $name, $city, $country ?: 'Indonesia', $id]);
                flash('success', 'Bandara diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO airports (code, name, city, country) VALUES (?,?,?,?)')
                    ->execute([$code, $name, $city, $country ?: 'Indonesia']);
                flash('success', 'Bandara ditambahkan.');
            }
        }
    }
    redirect(url('admin/airports.php'));
}

if (isset($_GET['delete'])) {
    $did = (int) $_GET['delete'];
    if ($did > 0) {
        try {
            $pdo->prepare('DELETE FROM airports WHERE id = ?')->execute([$did]);
            flash('success', 'Bandara dihapus.');
        } catch (Throwable $e) {
            flash('error', 'Tidak dapat menghapus: masih dipakai penerbangan.');
        }
    }
    redirect(url('admin/airports.php'));
}

$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    $st = $pdo->prepare('SELECT * FROM airports WHERE id = ? LIMIT 1');
    $st->execute([$eid]);
    $edit = $st->fetch();
}

$rows = $pdo->query('SELECT * FROM airports ORDER BY city')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title" style="font-size: 2rem; font-weight: 800;">Kelola Bandara</h1>

<div class="card" style="border: 2px solid var(--accent);">
    <h2 style="margin-top:0; color: var(--primary); font-size: 1.5rem; font-weight: 700;"><?= $edit ? 'Ubah bandara' : 'Tambah bandara baru' ?></h2>
    <p class="muted" style="margin-bottom: 1.5rem;"><?= $edit ? 'Edit informasi bandara yang sudah ada.' : 'Tambah bandara baru ke sistem penerbangan.' ?></p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
        <div class="form-row">
            <label><span style="font-weight: 700;">Kode IATA</span><input name="code" maxlength="5" required value="<?= h($edit['code'] ?? '') ?>" placeholder="CGK" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
            <label><span style="font-weight: 700;">Nama bandara</span><input name="name" maxlength="150" required value="<?= h($edit['name'] ?? '') ?>" placeholder="Soekarno-Hatta" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
        </div>
        <div class="form-row">
            <label><span style="font-weight: 700;">Kota</span><input name="city" maxlength="100" required value="<?= h($edit['city'] ?? '') ?>" placeholder="Jakarta" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
            <label><span style="font-weight: 700;">Negara</span><input name="country" maxlength="100" value="<?= h($edit['country'] ?? 'Indonesia') ?>" placeholder="Indonesia" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
        </div>
        <div class="actions-inline">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 8px;"><?= $edit ? 'Simpan perubahan' : 'Tambah bandara' ?></button>
            <?php if ($edit): ?>
                <a class="btn btn-ghost" href="<?= h(url('admin/airports.php')) ?>">Batal edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<h2 style="color: var(--primary); font-size: 1.5rem; font-weight: 700; margin: 2rem 0 1rem;">Daftar Bandara</h2>

<div class="grid-flights">
    <?php foreach ($rows as $r): ?>
        <article class="card flight-row">
            <div class="flight-row-main">
                <p class="flight-airline-line">
                    <strong style="font-size: 1.5rem;"><?= h($r['code']) ?></strong>
                    <span class="flight-detail-badge"><?= h(substr($r['code'], 0, 2)) ?></span>
                </p>
                <p style="font-weight: 600; color: var(--text); margin: 0.5rem 0; font-size: 1.1rem;"><?= h($r['name']) ?></p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= h($r['city']) ?></span>
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= h($r['country']) ?></span>
                </div>
            </div>
            <div class="actions-inline">
                <a class="btn btn-sm btn-ghost" href="<?= h(url('admin/airports.php?edit=' . (int) $r['id'])) ?>" style="font-weight: 600;">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url('admin/airports.php?delete=' . (int) $r['id'])) ?>" onclick="return confirm('Hapus bandara ini?');" style="font-weight: 600;">Hapus</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
