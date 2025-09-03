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

    // Validações mínimas (ajuste conforme a regra de negócio)
    $required = ['curso','nome','cpf','email','contato','administrador_id','cep','rua','bairro','numero','uf'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            sendResponse("Campo obrigatório ausente: {$field}", null, true, 422);
        }
    }

    // Normalizações simples
    $cpf     = preg_replace('/\D/', '', (string)($data['cpf'] ?? ''));
    $contato = preg_replace('/\D/', '', (string)($data['contato'] ?? ''));
    $cep     = preg_replace('/\D/', '', (string)($data['cep'] ?? ''));
    $uf      = strtoupper(substr((string)($data['uf'] ?? ''), 0, 2));

    // Geração de matrícula (ex.: 8 dígitos). Há UNIQUE constraint no banco; se colidir, tratamos erro.
    $matricula = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $con->beginTransaction();

    // 1) Inserir aluno
    $stmt = $con->prepare("
        INSERT INTO aluno (
            matricula,
            curso,
            nome,
            cpf,
            email,
            contato,
            administrador_id
        ) VALUES (
            :matricula,
            :curso,
            :nome,
            :cpf,
            :email,
            :contato,
            :administrador_id
        )
    ");

    $stmt->execute([
        'matricula'        => $matricula,
        'curso'            => $data['curso_aluno'],
        'nome'             => $data['nome_aluno'],
        'cpf'              => $cpf,
        'email'            => $data['email_aluno'],
        'contato'          => $contato,
        'administrador_id' => (int)$data['administrador_id'],
    ]);

    $alunoId = (int)$con->lastInsertId();

    // 2) Inserir endereço
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

    // 3) Relacionar aluno ↔ endereço
    $stmt = $con->prepare("
        INSERT INTO aluno_endereco (aluno_id, endereco_id)
        VALUES (:aluno_id, :endereco_id)
    ");

    $stmt->execute([
        'aluno_id'    => $alunoId,
        'endereco_id' => $enderecoId,
    ]);

    $con->commit();

    sendResponse(
        'Aluno cadastrado com sucesso!',
        ['aluno_id' => $alunoId, 'endereco_id' => $enderecoId, 'matricula' => $matricula],
        false,
        201
    );

} catch (Throwable $th) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }

    // Trata colisão de UNIQUE (matricula, cpf, email)
    $msg = 'Erro ao cadastrar aluno.';
    if ($th instanceof PDOException && (int)$th->getCode() === 23000) {
        $msg = 'Dados já cadastrados (verifique matrícula/CPF/Email).';
    }

    sendResponse($msg, null, true, 500);
}
