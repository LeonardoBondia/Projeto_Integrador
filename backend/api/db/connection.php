<?php

$pdo = null;

function getConnection(): PDO
{
    global $pdo;

    // move p .env em produção
    $hostname = 'db';
    $database = 'pi';
    $username = 'pi.dev';
    $password = '123';

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = "mysql:host={$hostname};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    return $pdo;
}
