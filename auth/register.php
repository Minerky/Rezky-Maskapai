<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if (!empty($_SESSION['user'])) {
    $slug = $_SESSION['user']['role_slug'] ?? '';
    redirect($slug === 'admin' ? url('admin/dashboard.php') : url('user/index.php'));
}

$pageTitle = 'Daftar — Rezky Maskapai';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $nik = trim((string) ($_POST['nik'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password_confirm'] ?? '');

        $nameLen = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
        if ($name === '' || $nameLen < 3) {
            $errors[] = 'Nama lengkap minimal 3 karakter.';
        }
        if (!validate_email($email)) {
            $errors[] = 'Email tidak valid.';
        }
        if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK harus 16 digit angka.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'Kata sandi minimal 8 karakter.';
        }
        if ($pass !== $pass2) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        if (!$errors) {
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors[] = 'Email sudah terdaftar.';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $ins = $pdo->prepare(
                    'INSERT INTO users (role_id, full_name, email, phone, nik, password_hash) VALUES (2, ?, ?, ?, ?, ?)'
                );
                $ins->execute([$name, $email, $phone ?: null, $nik ?: null, $hash]);
                flash('success', 'Pendaftaran berhasil. Silakan masuk.');
                redirect(url('auth/login.php'));
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-page">
    <section class="auth-card">
        <div class="auth-header">
            <h1>Daftar Akun</h1>
            <p class="auth-subtitle">Bergabung dengan Rezky Maskapai</p>
        </div>
        <p class="muted">Buat akun penumpang untuk mulai memesan tiket penerbangan dengan mudah.</p>
        <?php if ($errors): ?>
            <div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" class="form-grid" novalidate>
            <?= csrf_field() ?>
            <label>
                <span>Nama lengkap</span>
                <input type="text" name="full_name" required maxlength="120" value="<?= h($_POST['full_name'] ?? '') ?>" placeholder="Nama lengkap Anda">
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" required maxlength="190" value="<?= h($_POST['email'] ?? '') ?>" placeholder="nama@email.com">
            </label>
            <label>
                <span>No. HP</span>
                <input type="text" name="phone" maxlength="30" value="<?= h($_POST['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
            </label>
            <label>
                <span>NIK (Nomor Induk Kependudukan)</span>
                <input type="text" name="nik" maxlength="16" pattern="\d{16}" value="<?= h($_POST['nik'] ?? '') ?>" placeholder="16 digit angka">
            </label>
            <label>
                <span>Kata sandi</span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter">
            </label>
            <label>
                <span>Ulangi kata sandi</span>
                <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi">
            </label>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="<?= h(url('auth/login.php')) ?>">Masuk</a></p>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
