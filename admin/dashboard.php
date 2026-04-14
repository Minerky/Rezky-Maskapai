<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Dashboard Admin — Rezky Maskapai';

$stats = [
    'users' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2")->fetchColumn(),
    'flights' => (int) $pdo->query("SELECT COUNT(*) FROM flights WHERE status = 'active'")->fetchColumn(),
    'bookings_today' => (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'await_pay' => (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'awaiting_verification'")->fetchColumn(),
];
$rev = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'approved'")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Dashboard Admin</h1>

<div class="stat-grid">
    <div class="stat-card">
        <span>Pengguna penumpang</span>
        <strong><?= $stats['users'] ?></strong>
    </div>
    <div class="stat-card">
        <span>Penerbangan aktif</span>
        <strong><?= $stats['flights'] ?></strong>
    </div>
    <div class="stat-card">
        <span>Pesanan hari ini</span>
        <strong><?= $stats['bookings_today'] ?></strong>
    </div>
    <div class="stat-card">
        <span>Menunggu verifikasi</span>
        <strong><?= $stats['await_pay'] ?></strong>
    </div>
    <div class="stat-card">
        <span>Total pendapatan</span>
        <strong>Rp <?= h(number_format((float) $rev, 0, ',', '.')) ?></strong>
    </div>
</div>

<div class="card">
    <h2 style="margin-top:0; color: var(--primary);">Langkah cepat</h2>
    <div style="display: grid; gap: 1rem; margin-top: 1.5rem;">
        <a class="quick-action-card" href="<?= h(url('admin/transactions.php')) ?>">
            <strong>Verifikasi Pembayaran</strong>
            <p class="muted" style="font-size: 0.9rem; margin: 0.25rem 0 0;">Setujui atau tolak bukti pembayaran tiket</p>
        </a>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a class="quick-action-card" href="<?= h(url('admin/flights.php')) ?>">
                <strong>Kelola Penerbangan</strong>
                <p class="muted" style="font-size: 0.85rem; margin: 0.25rem 0 0;">Tambah atau edit jadwal penerbangan</p>
            </a>
            <a class="quick-action-card" href="<?= h(url('admin/airports.php')) ?>">
                <strong>Kelola Bandara</strong>
                <p class="muted" style="font-size: 0.85rem; margin: 0.25rem 0 0;">Tambah atau edit bandara</p>
            </a>
            <a class="quick-action-card" href="<?= h(url('admin/airlines.php')) ?>">
                <strong>Kelola Maskapai</strong>
                <p class="muted" style="font-size: 0.85rem; margin: 0.25rem 0 0;">Tambah atau edit maskapai</p>
            </a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
