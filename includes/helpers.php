<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_environment(): string
{
    return getenv('APP_ENV') ?: 'production';
}

function safe_db(): ?PDO
{
    try {
        return db();
    } catch (Throwable $exception) {
        error_log('No fue posible conectar con MySQL: ' . $exception->getMessage());
        return null;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($message) ? $message : null;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return is_string($path) ? basename($path) : 'index.php';
}

function nav_class(string $file): string
{
    $current = current_path();
    $isHome = ($current === '' || $current === 'index.php') && $file === 'index.php';
    return ($current === $file || $isHome) ? 'nav__link nav__link--active' : 'nav__link';
}

function format_price(mixed $price): string
{
    if ($price === null || $price === '') {
        return 'Consultar';
    }

    return 'US$ ' . number_format((float) $price, 2, ',', '.');
}

function format_book_type(string $type): string
{
    $labels = [
        'business' => 'Negocios',
        'mod_cook' => 'Cocina moderna',
        'popular_comp' => 'Computación',
        'psychology' => 'Psicología',
        'trad_cook' => 'Cocina tradicional',
        'UNDECIDED' => 'Sin clasificar',
    ];

    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

function text_length(string $text): int
{
    return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
}

function text_substr(string $text, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, $start, $length);
    }

    return $length === null ? substr($text, $start) : substr($text, $start, $length);
}

function text_upper(string $text): string
{
    return function_exists('mb_strtoupper') ? mb_strtoupper($text) : strtoupper($text);
}

function truncate_text(string $text, int $length = 150): string
{
    $text = trim($text);

    if (text_length($text) <= $length) {
        return $text;
    }

    return rtrim(text_substr($text, 0, $length - 1)) . '…';
}
