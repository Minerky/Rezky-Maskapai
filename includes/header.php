<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'Rezky Maskapai';
$isAdminArea = !empty($isAdminArea);
$u = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= h(asset('assets/css/style.css')) ?>">
    <link rel="icon" type="image/jpeg" href="https://img.freepik.com/vektor-premium/logo-pesawat-terbang_640251-15493.jpg">
</head>
<body class="<?= $isAdminArea ? 'admin-body' : '' ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= h($isAdminArea ? url('admin/dashboard.php') : url('user/index.php')) ?>">
            <img class="brand-mark" src="https://img.freepik.com/vektor-premium/logo-pesawat-terbang_640251-15493.jpg" alt="Rezky Maskapai" width="40" height="40" decoding="async">
            <div class="brand-content">
                <span class="brand-text">Rezky Maskapai</span>
                <span class="brand-tagline">Terbang dengan Nyaman</span>
            </div>
        </a>
        <nav class="nav-main">
            <?php if ($u): ?>
                <?php if (($u['role_slug'] ?? '') === 'admin' && $isAdminArea): ?>
                    <a href="<?= h(url('admin/dashboard.php')) ?>">Dashboard</a>
                    <a href="<?= h(url('admin/airports.php')) ?>">Bandara</a>
                    <a href="<?= h(url('admin/airlines.php')) ?>">Maskapai</a>
                    <a href="<?= h(url('admin/flights.php')) ?>">Penerbangan</a>
                    <a href="<?= h(url('admin/transactions.php')) ?>">Transaksi</a>
                    <a href="<?= h(url('admin/ticket_manage.php')) ?>">Tiket</a>
                    <a href="<?= h(url('admin/users.php')) ?>">Pengguna</a>
                <?php else: ?>
                    <?php if (($u['role_slug'] ?? '') === 'admin'): ?>
                        <a href="<?= h(url('admin/dashboard.php')) ?>">Panel Admin</a>
                    <?php endif; ?>
                    <a href="<?= h(url('user/index.php')) ?>">Jadwal</a>
                    <a href="<?= h(url('user/history.php')) ?>">Riwayat</a>
                    <a href="<?= h(url('user/profile.php')) ?>">Profil</a>
                <?php endif; ?>
                <div class="nav-user-section">
                    <div class="nav-user-avatar">
                        <span class="nav-user-initial"><?= strtoupper(substr(h($u['full_name'] ?? 'U'), 0, 1)) ?></span>
                    </div>
                    <div class="nav-user-info">
                        <span class="nav-user-name"><?= h($u['full_name'] ?? '') ?></span>
                        <a class="nav-logout-link" href="<?= h(url('auth/logout.php')) ?>">Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= h(url('user/index.php')) ?>">Beranda</a>
                <a href="<?= h(url('auth/login.php')) ?>">Masuk</a>
                <a class="btn btn-primary btn-sm" href="<?= h(url('auth/register.php')) ?>">Daftar</a>
            <?php endif; ?>
        </nav>
        <button type="button" class="nav-toggle" aria-label="Menu" data-nav-toggle></button>
    </div>
</header>
<main class="main-content">
    <div class="container">
        <?php
        foreach (['success', 'error', 'info'] as $fk) {
            $msg = flash($fk);
            if ($msg) {
                echo '<div class="alert alert-' . h($fk === 'error' ? 'error' : ($fk === 'success' ? 'success' : 'info')) . '">' . h($msg) . '</div>';
            }
        }
        ?>
