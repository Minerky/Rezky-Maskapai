<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Status Tiket — Rezky Maskapai';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $tid = (int) ($_POST['ticket_id'] ?? 0);
    $stNew = (string) ($_POST['ticket_status'] ?? '');
    if (!in_array($stNew, ['inactive', 'active', 'cancelled'], true) || $tid < 1) {
        flash('error', 'Data tidak valid.');
    } else {
        if ($stNew === 'active') {
            $pdo->prepare(
                "UPDATE tickets SET status = 'active', issued_at = COALESCE(issued_at, NOW()) WHERE id = ?"
            )->execute([$tid]);
        } else {
            $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$stNew, $tid]);
        }
        flash('success', 'Status tiket diperbarui.');
    }
    redirect(url('admin/ticket_manage.php'));
}

$sql = "SELECT t.id, t.ticket_code, t.status, t.issued_at, b.booking_code, b.status AS bstatus,
               u.full_name, u.email, f.flight_number
        FROM tickets t
        JOIN bookings b ON b.id = t.booking_id
        JOIN users u ON u.id = t.user_id
        JOIN flights f ON f.id = b.flight_id
        ORDER BY t.id DESC";
$rows = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title" style="font-size: 2rem; font-weight: 800;">Kelola Tiket</h1>
<p class="muted" style="font-size: 1rem; margin-bottom: 1.5rem;">Ubah status tiket untuk mengelola penerbitan dan pembatalan tiket penumpang.</p>

<div class="grid-flights">
    <?php foreach ($rows as $r): ?>
        <?php
        $statusBadge = '';
        $statusColor = '';
        switch ($r['status']) {
            case 'active':
                $statusBadge = 'Aktif';
                $statusColor = '#dcfce7';
                $statusText = '#166534';
                break;
            case 'inactive':
                $statusBadge = 'Nonaktif';
                $statusColor = '#f1f5f9';
                $statusText = 'var(--muted)';
                break;
            case 'cancelled':
                $statusBadge = 'Dibatalkan';
                $statusColor = '#fee2e2';
                $statusText = '#991b1b';
                break;
        }
        ?>
        <article class="card flight-row">
            <div class="flight-row-main">
                <p class="flight-airline-line">
                    <strong style="font-size: 1.5rem;"><?= h($r['ticket_code']) ?></strong>
                    <span class="flight-detail-badge">Tiket</span>
                </p>
                <p style="font-weight: 600; color: var(--text); margin: 0.5rem 0; font-size: 1.1rem;"><?= h($r['full_name']) ?></p>
                <p class="muted" style="margin: 0.25rem 0;"><?= h($r['email']) ?></p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= h($r['booking_code']) ?></span>
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= h($r['flight_number']) ?></span>
                    <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 600;"><?= h($r['bstatus']) ?></span>
                    <span class="badge" style="background: <?= $statusColor ?>; color: <?= $statusText ?>; font-weight: 600;"><?= $statusBadge ?></span>
                </div>
            </div>
            <div>
                <form method="post" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ticket_id" value="<?= (int) $r['id'] ?>">
                    <select name="ticket_status" style="font-weight: 600; padding: 0.5rem 0.75rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                        <option value="inactive" <?= $r['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                        <option value="active" <?= $r['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="cancelled" <?= $r['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary" style="font-weight: 700; padding: 0.5rem 1rem; border-radius: 8px;">Simpan</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
