<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/require_admin.php';

$isAdminArea = true;
$pageTitle = 'Transaksi — Rezky Maskapai';

$sql = "SELECT b.id, b.booking_code, b.seat_count, b.total_amount, b.status AS bstatus, b.created_at,
               p.id AS payment_id, p.status AS pstatus, p.amount, p.payment_method,
               u.full_name, u.email,
               f.flight_number, f.departure_datetime,
               ao.city AS ocity, ad.city AS dcity,
               (SELECT pp2.stored_filename FROM payment_proofs pp2
                WHERE pp2.payment_id = p.id ORDER BY pp2.id DESC LIMIT 1) AS stored_filename
        FROM bookings b
        JOIN payments p ON p.booking_id = b.id
        JOIN users u ON u.id = b.user_id
        JOIN flights f ON f.id = b.flight_id
        JOIN airports ao ON ao.id = f.origin_airport_id
        JOIN airports ad ON ad.id = f.destination_airport_id
        ORDER BY b.created_at DESC";
$rows = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title" style="font-size: 2rem; font-weight: 800;">Kelola Transaksi</h1>
<p class="muted" style="font-size: 1rem; margin-bottom: 1.5rem;">Verifikasi pembayaran dan kelola status transaksi pemesanan tiket.</p>

<div class="grid-flights">
    <?php foreach ($rows as $r): ?>
        <?php
        $bstatusColor = '';
        $bstatusText = '';
        $pstatusColor = '';
        $pstatusText = '';
        
        switch ($r['bstatus']) {
            case 'confirmed':
                $bstatusColor = '#dcfce7';
                $bstatusText = '#166534';
                break;
            case 'awaiting_verification':
                $bstatusColor = '#fef3c7';
                $bstatusText = '#92400e';
                break;
            case 'cancelled':
                $bstatusColor = '#fee2e2';
                $bstatusText = '#991b1b';
                break;
            default:
                $bstatusColor = '#f1f5f9';
                $bstatusText = 'var(--muted)';
        }
        
        switch ($r['pstatus']) {
            case 'approved':
                $pstatusColor = '#dcfce7';
                $pstatusText = '#166534';
                break;
            case 'awaiting_verification':
                $pstatusColor = '#fef3c7';
                $pstatusText = '#92400e';
                break;
            case 'rejected':
                $pstatusColor = '#fee2e2';
                $pstatusText = '#991b1b';
                break;
            default:
                $pstatusColor = '#f1f5f9';
                $pstatusText = 'var(--muted)';
        }
        ?>
        <article class="card flight-row">
            <div class="flight-row-main">
                <p class="flight-airline-line">
                    <strong style="font-size: 1.5rem;"><?= h($r['booking_code']) ?></strong>
                    <span class="flight-detail-badge">Booking</span>
                </p>
                <p style="font-weight: 600; color: var(--text); margin: 0.5rem 0; font-size: 1.1rem;"><?= h($r['full_name']) ?></p>
                <p class="muted" style="margin: 0.25rem 0;"><?= h($r['email']) ?></p>
                <div class="flight-route" style="margin-top: 0.5rem;">
                    <div class="flight-endpoint">
                        <strong><?= h($r['ocity']) ?></strong>
                    </div>
                    <span class="flight-route-arrow">→</span>
                    <div class="flight-endpoint">
                        <strong><?= h($r['dcity']) ?></strong>
                    </div>
                </div>
                <p class="muted" style="margin: 0.25rem 0;"><?= h($r['flight_number']) ?> · <?= h(date('d M Y H:i', strtotime($r['created_at']))) ?></p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem;">
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;">Rp <?= h(number_format((float) $r['total_amount'], 0, ',', '.')) ?></span>
                    <span class="badge" style="background: #e0f2fe; color: var(--primary); font-weight: 600;"><?= h(payment_method_label((string) ($r['payment_method'] ?? 'bank_transfer'))) ?></span>
                    <span class="badge" style="background: <?= $bstatusColor ?>; color: <?= $bstatusText ?>; font-weight: 600;"><?= h($r['bstatus']) ?></span>
                    <span class="badge" style="background: <?= $pstatusColor ?>; color: <?= $pstatusText ?>; font-weight: 600;"><?= h($r['pstatus']) ?></span>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php if (!empty($r['stored_filename'])): ?>
                    <a target="_blank" href="<?= h(asset('uploads/proofs/' . $r['stored_filename'])) ?>" class="btn btn-sm btn-ghost" style="font-weight: 600;">Lihat bukti</a>
                <?php endif; ?>
                <?php if ($r['bstatus'] === 'awaiting_verification' && $r['pstatus'] === 'awaiting_verification'): ?>
                    <form method="post" action="<?= h(url('admin/payment_action.php')) ?>" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="booking_id" value="<?= (int) $r['id'] ?>">
                        <input type="text" name="admin_note" placeholder="Catatan (opsional)" maxlength="240" style="font-weight: 600; padding: 0.5rem 0.75rem; border: 2px solid var(--accent); border-radius: 8px; background: #f8fafc;">
                        <div class="actions-inline">
                            <button class="btn btn-sm btn-success" name="decision" value="approve" style="font-weight: 700; padding: 0.5rem 1rem; border-radius: 8px;">Setujui</button>
                            <button class="btn btn-sm btn-danger" name="decision" value="reject" onclick="return confirm('Tolak pembayaran ini?');" style="font-weight: 700; padding: 0.5rem 1rem; border-radius: 8px;">Tolak</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
