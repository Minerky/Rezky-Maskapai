<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if (!empty($_SESSION['user'])) {
    $slug = $_SESSION['user']['role_slug'] ?? '';
    redirect($slug === 'admin' ? url('admin/dashboard.php') : url('user/index.php'));
}

$pageTitle = 'Masuk — Rezky Maskapai';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!validate_email($email) || $password === '') {
            $error = 'Email dan kata sandi wajib diisi dengan benar.';
        } else {
            $st = $pdo->prepare(
                'SELECT u.id, u.full_name, u.email, u.password_hash, u.is_active, r.slug AS role_slug
                 FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ? LIMIT 1'
            );
            $st->execute([$email]);
            $row = $st->fetch();
            if (!$row || !(bool) $row['is_active']) {
                $error = 'Email atau kata sandi salah.';
            } elseif (!password_verify($password, $row['password_hash'])) {
                $error = 'Email atau kata sandi salah.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $row['id'],
                    'full_name' => $row['full_name'],
                    'email' => $row['email'],
                    'role_slug' => $row['role_slug'],
                ];
                flash('success', 'Selamat datang, ' . $row['full_name'] . '!');
                if ($row['role_slug'] === 'admin') {
                    redirect(url('admin/dashboard.php'));
                }
                redirect(url('user/index.php'));
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-page">
    <section class="auth-card">
        <div class="auth-header">
            <h1>Masuk</h1>
            <p class="auth-subtitle">Selamat datang kembali di Rezky Maskapai</p>
        </div>
        <p class="muted">Silakan masuk untuk melanjutkan pemesanan tiket penerbangan Anda.</p>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <label>
                <span>Email</span>
                <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>" autocomplete="username" placeholder="nama@email.com">
            </label>
            <label>
                <span>Kata sandi</span>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Minimal 8 karakter">
            </label>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
        <p class="auth-switch">Belum punya akun penumpang? <a href="<?= h(url('auth/register.php')) ?>">Daftar sekarang</a></p>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
