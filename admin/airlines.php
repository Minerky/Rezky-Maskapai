<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Kelola Maskapai — Rezky Maskapai';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
    if (strlen($name) < 2 || strlen($code) < 2) {
        flash('error', 'Data tidak valid.');
    } else {
        if ($id > 0) {
            $pdo->prepare('UPDATE airlines SET name=?, code=? WHERE id=?')->execute([$name, $code, $id]);
            flash('success', 'Maskapai diperbarui.');
        } else {
            $pdo->prepare('INSERT INTO airlines (name, code) VALUES (?,?)')->execute([$name, $code]);
            flash('success', 'Maskapai ditambahkan.');
        }
    }
    redirect(url('admin/airlines.php'));
}

if (isset($_GET['delete'])) {
    $did = (int) $_GET['delete'];
    if ($did > 0) {
        try {
            $pdo->prepare('DELETE FROM airlines WHERE id = ?')->execute([$did]);
            flash('success', 'Maskapai dihapus.');
        } catch (Throwable $e) {
            flash('error', 'Tidak dapat menghapus: masih dipakai penerbangan.');
        }
    }
    redirect(url('admin/airlines.php'));
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch();
}

$rows = $pdo->query('SELECT * FROM airlines ORDER BY name')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title" style="font-size: 2rem; font-weight: 800;">Kelola Maskapai</h1>

<div class="card" style="border: 2px solid var(--accent);">
    <h2 style="margin-top:0; color: var(--primary); font-size: 1.5rem; font-weight: 700;"><?= $edit ? 'Ubah maskapai' : 'Tambah maskapai baru' ?></h2>
    <p class="muted" style="margin-bottom: 1.5rem;"><?= $edit ? 'Edit informasi maskapai yang sudah ada.' : 'Tambah maskapai baru ke sistem penerbangan.' ?></p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>">
        <div class="form-row">
            <label><span style="font-weight: 700;">Nama maskapai</span><input name="name" maxlength="120" required value="<?= h($edit['name'] ?? '') ?>" placeholder="Garuda Indonesia" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
            <label><span style="font-weight: 700;">Kode maskapai</span><input name="code" maxlength="5" required value="<?= h($edit['code'] ?? '') ?>" placeholder="GA" style="font-weight: 600; padding: 0.75rem 1rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc; transition: all 0.2s ease;"></label>
        </div>
        <div class="actions-inline">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 1.5rem; border-radius: 8px;"><?= $edit ? 'Simpan perubahan' : 'Tambah maskapai' ?></button>
            <?php if ($edit): ?>
                <a class="btn btn-ghost" href="<?= h(url('admin/airlines.php')) ?>">Batal edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<h2 style="color: var(--primary); font-size: 1.5rem; font-weight: 700; margin: 2rem 0 1rem;">Daftar Maskapai</h2>

<div class="grid-flights">
    <?php foreach ($rows as $r): ?>
        <article class="card flight-row">
            <div class="flight-row-main">
                <p class="flight-airline-line">
                    <strong style="font-size: 1.5rem;"><?= h($r['code']) ?></strong>
                    <span class="flight-detail-badge"><?= h(substr($r['code'], 0, 1)) ?></span>
                </p>
                <p style="font-weight: 600; color: var(--text); margin: 0.5rem 0; font-size: 1.1rem;"><?= h($r['name']) ?></p>
            </div>
            <div class="actions-inline">
                <a class="btn btn-sm btn-ghost" href="<?= h(url('admin/airlines.php?edit=' . (int) $r['id'])) ?>" style="font-weight: 600;">Edit</a>
                <a class="btn btn-sm btn-danger" href="<?= h(url('admin/airlines.php?delete=' . (int) $r['id'])) ?>" onclick="return confirm('Hapus maskapai ini?');" style="font-weight: 600;">Hapus</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
