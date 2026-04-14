<?php
declare(strict_types=1);

if (empty($_SESSION['user']) || ($_SESSION['user']['role_slug'] ?? '') !== 'user') {
    flash('error', 'Silakan masuk sebagai penumpang.');
    redirect(url('auth/login.php'));
}
