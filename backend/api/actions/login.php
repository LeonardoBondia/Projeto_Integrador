<?php

require_once('../db/connection.php');
require('../utils/helpers.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('Método inválido', null, true, 405);
}

try {
    $con  = getConnection();
    $data = getBody();

    $email = $data['email'] ?? '';
    $senha = $data['senha'] ?? '';

    $stmt = $con->prepare('SELECT id, email, senha FROM administrador WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $result = $stmt->fetch();

    if (!$result || !password_verify($senha, $result['senha'])) {
        sendResponse('Email/Senha inválida.', null, true, 401);
    }

    // Cookie seguro (ajuste 'secure' => true em HTTPS)
    setcookie('auth', (string)$result['id'], [
        'expires'  => time() + 3600,
        'path'     => '/',
        'httponly' => true,
        'secure'   => false, // true em produção HTTPS
        'samesite' => 'Lax',
    ]);

    sendResponse('Login realizado com sucesso.', [
        'id'    => $result['id'],
        'email' => $result['email'],
    ], false, 200);

} catch (Throwable $th) {
    sendResponse('Erro ao se conectar ao banco de dados.', null, true, 500);
}
