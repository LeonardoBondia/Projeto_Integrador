<?php

function dd(mixed ...$items): void
{
    echo '<pre>';
    foreach ($items as $item) {
        var_dump($item);
    }
    echo '</pre>';
    exit;
}

function sendResponse(string $message, mixed $data, bool $error, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['message' => $message, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

function getBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function isAuth(): bool
{
    return isset($_COOKIE['auth']);
}
