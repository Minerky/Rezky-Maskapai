<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user'])) {
    $slug = $_SESSION['user']['role_slug'] ?? '';
    header('Location: ' . ($slug === 'admin' ? url('admin/dashboard.php') : url('user/index.php')));
} else {
    header('Location: ' . url('user/index.php'));
}
exit;
