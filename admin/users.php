<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Pengguna — Rezky Maskapai';
$meId = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $uid = (int) ($_POST['user_id'] ?? 0);
    $active = (int) ($_POST['is_active'] ?? 1) === 1 ? 1 : 0;
    if ($uid > 0 && $uid !== $meId) {
        $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$active, $uid]);
        flash('success', 'Status pengguna diperbarui.');
    } else {
        flash('error', 'Tidak dapat mengubah akun Anda sendiri di sini.');
    }
    redirect(url('admin/users.php'));
}

$rows = $pdo->query(
    "SELECT u.id, u.full_name, u.email, u.phone, u.is_active, u.created_at, r.name AS role_name, r.slug
     FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id"
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Kelola pengguna</h1>

<div class="table-wrap">
    <table class="data">
        <thead>
            <tr><th>ID</th><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= h($r['full_name']) ?></td>
                    <td><?= h($r['email']) ?></td>
                    <td><?= h($r['role_name']) ?></td>
                    <td><?= (int) $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
                    <td>
                        <?php if ((int) $r['id'] === $meId): ?>
                            <span class="muted">Anda</span>
                        <?php elseif ($r['slug'] === 'admin'): ?>
                            <span class="muted">Admin</span>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>">
                                <?php if ((int) $r['is_active']): ?>
                                    <input type="hidden" name="is_active" value="0">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Nonaktifkan pengguna?');">Nonaktifkan</button>
                                <?php else: ?>
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
