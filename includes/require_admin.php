<?php
declare(strict_types=1);

if (empty($_SESSION['user']) || ($_SESSION['user']['role_slug'] ?? '') !== 'admin') {
    flash('error', 'Akses admin diperlukan.');
    redirect(url('auth/login.php'));
}
