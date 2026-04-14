<?php
declare(strict_types=1);

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function asset(string $path): string
{
    return rtrim(PUBLIC_PATH, '/') . '/' . ltrim($path, '/');
}

function url(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $t = $_POST['csrf_token'] ?? '';
    return is_string($t) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t);
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $m = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $m !== null ? (string) $m : null;
}

function generate_booking_code(PDO $pdo): string
{
    do {
        $code = 'BK' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
        $st = $pdo->prepare('SELECT id FROM bookings WHERE booking_code = ? LIMIT 1');
        $st->execute([$code]);
    } while ($st->fetch());
    return $code;
}

function generate_ticket_code(PDO $pdo): string
{
    do {
        $code = 'TK' . date('ym') . strtoupper(bin2hex(random_bytes(4)));
        $st = $pdo->prepare('SELECT id FROM tickets WHERE ticket_code = ? LIMIT 1');
        $st->execute([$code]);
    } while ($st->fetch());
    return $code;
}

function require_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
        http_response_code(403);
        exit('Permintaan tidak valid.');
    }
}

function validate_email(string $e): bool
{
    return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
}

function flight_available_seats(PDO $pdo, int $flightId, ?int $excludeBookingId = null): int
{
    $sql = "SELECT (f.seat_capacity - COALESCE(SUM(b.seat_count), 0)) AS avail
            FROM flights f
            LEFT JOIN bookings b ON b.flight_id = f.id
              AND b.status IN ('pending_payment','awaiting_verification','confirmed')";
    if ($excludeBookingId) {
        $sql .= ' AND b.id <> :ex';
    }
    $sql .= ' WHERE f.id = :fid GROUP BY f.id, f.seat_capacity';
    $st = $pdo->prepare($sql);
    $st->bindValue(':fid', $flightId, PDO::PARAM_INT);
    if ($excludeBookingId) {
        $st->bindValue(':ex', $excludeBookingId, PDO::PARAM_INT);
    }
    $st->execute();
    $row = $st->fetch();
    return $row ? max(0, (int) $row['avail']) : 0;
}
