<?php
require_once('backend/api/db/connection.php');
require('backend/api/utils/helpers.php');

if (!isAuth()) {
    sendResponse('Não autorizado!', null, true, 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('Método inválido', null, true, 405);
}

try {
    $con  = getConnection();
    $data = getBody();

    // Validações mínimas
    $required = ['nome','cpf','email','contato','administrador_id','cep','rua','bairro','numero','uf'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            sendResponse("Campo obrigatório ausente: {$field}", null, true, 422);
        }
    }

    // Normalizações
    $cpf     = preg_replace('/\D/', '', (string)($data['cpf'] ?? ''));
    $contato = preg_replace('/\D/', '', (string)($data['contato'] ?? ''));
    $cep     = preg_replace('/\D/', '', (string)($data['cep'] ?? ''));
    $uf      = strtoupper(substr((string)($data['uf'] ?? ''), 0, 2));

    $con->beginTransaction();

    // 1) Endereço
    $stmt = $con->prepare("
        INSERT INTO endereco (
            cep,
            rua,
            bairro,
            numero,
            uf,
            complemento
        ) VALUES (
            :cep,
            :rua,
            :bairro,
            :numero,
            :uf,
            :complemento
        )
    ");
    $stmt->execute([
        'cep'         => $cep,
        'rua'         => $data['rua'],
        'bairro'      => $data['bairro'],
        'numero'      => (string)$data['numero'],
        'uf'          => $uf,
        'complemento' => $data['complemento'] ?? null,
    ]);
    $enderecoId = (int)$con->lastInsertId();

    // 2) Professor
    $stmt = $con->prepare("
        INSERT INTO professor (
            nome,
            cpf,
            email,
            contato,
            administrador_id
        ) VALUES (
            :nome,
            :cpf,
            :email,
            :contato,
            :administrador_id
        )
    ");
    $stmt->execute([
        'nome'             => $data['nome_professor'],
        'cpf'              => $cpf,
        'email'            => $data['email_professor'],
        'contato'          => $contato['contato_professor'],
        'administrador_id' => (int)$data['administrador_id'],
    ]);
    $professorId = (int)$con->lastInsertId();

    // 3) Relacionar professor ↔ endereço
    $stmt = $con->prepare("
        INSERT INTO professor_endereco (professor_id, endereco_id)
        VALUES (:professor_id, :endereco_id)
    ");
    $stmt->execute([
        'professor_id' => $professorId,
        'endereco_id'  => $enderecoId,
    ]);

    $con->commit();

    sendResponse(
        'Professor cadastrado com sucesso!',
        ['professor_id' => $professorId, 'endereco_id' => $enderecoId],
        false,
        201
    );

} catch (Throwable $th) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }

    $msg = 'Erro ao cadastrar professor.';
    if ($th instanceof PDOException && (int)$th->getCode() === 23000) {
        $msg = 'Dados já cadastrados (verifique CPF/Email).';
    }

    sendResponse($msg, null, true, 500);
}
