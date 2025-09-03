<?php
require('../utils/helpers.php');

setcookie('auth', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'httponly' => true,
    'secure'   => false, // true em produção HTTPS
    'samesite' => 'Lax',
]);

sendResponse('Logout realizado com sucesso!', null, false, 200);
