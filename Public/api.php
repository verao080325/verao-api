<?php

require_once '../App/LicencaManager.php';
header('Content-Type: application/json');

// Exemplo de endpoint GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['rota'] === 'verificar') {
    echo json_encode(['status' => 'API Verão está online']);
    exit;
}

// Exemplo de endpoint POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['rota'] === 'validar') {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!isset($dados['licenca'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Licença não fornecida']);
        exit;
    }

    $resultado = LicencaManager::gerarLicenca($dados['licenca']);
    echo json_encode($resultado);
    exit;
}
