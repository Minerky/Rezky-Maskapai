<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_user.php';

$userId = (int) $_SESSION['user']['id'];
$pageTitle = 'Profil — Rezky Maskapai';
$errors = [];

$st = $pdo->prepare('SELECT id, full_name, email, phone FROM users WHERE id = ? LIMIT 1');
$st->execute([$userId]);
$me = $st->fetch();
if (!$me) {
    redirect(url('auth/logout.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Token tidak valid.';
    } else {
        $action = (string) ($_POST['action'] ?? 'profile');
        if ($action === 'profile') {
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            if (strlen($name) < 3) {
                $errors[] = 'Nama minimal 3 karakter.';
            }
            if (!$errors) {
                $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?')->execute([$name, $phone ?: null, $userId]);
                $_SESSION['user']['full_name'] = $name;
                flash('success', 'Profil diperbarui.');
                redirect(url('user/profile.php'));
            }
        } elseif ($action === 'password') {
            $old = (string) ($_POST['old_password'] ?? '');
            $p1 = (string) ($_POST['new_password'] ?? '');
            $p2 = (string) ($_POST['new_password2'] ?? '');
            $st2 = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $st2->execute([$userId]);
            $row = $st2->fetch();
            if (!$row || !password_verify($old, $row['password_hash'])) {
                $errors[] = 'Kata sandi lama salah.';
            } elseif (strlen($p1) < 8) {
                $errors[] = 'Kata sandi baru minimal 8 karakter.';
            } elseif ($p1 !== $p2) {
                $errors[] = 'Konfirmasi kata sandi tidak cocok.';
            } else {
                $hash = password_hash($p1, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
                flash('success', 'Kata sandi berhasil diubah.');
                redirect(url('user/profile.php'));
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Profil pengguna</h1>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div>
<?php endif; ?>

<div class="card">
    <h2 style="margin-top:0;">Data diri</h2>
    <p class="muted">Email: <strong><?= h($me['email']) ?></strong> (email tidak dapat diubah di sini)</p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">
        <label>
            <span>Nama lengkap</span>
            <input type="text" name="full_name" required maxlength="120" value="<?= h($_POST['full_name'] ?? $me['full_name']) ?>">
        </label>
        <label>
            <span>No. HP</span>
            <input type="text" name="phone" maxlength="30" value="<?= h($_POST['phone'] ?? (string) $me['phone']) ?>">
        </label>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<div class="card">
    <h2 style="margin-top:0;">Ubah kata sandi</h2>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <label>
            <span>Kata sandi saat ini</span>
            <input type="password" name="old_password" required autocomplete="current-password">
        </label>
        <label>
            <span>Kata sandi baru</span>
            <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
        </label>
        <label>
            <span>Ulangi kata sandi baru</span>
            <input type="password" name="new_password2" required minlength="8" autocomplete="new-password">
        </label>
        <button type="submit" class="btn btn-secondary">Ubah sandi</button>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
