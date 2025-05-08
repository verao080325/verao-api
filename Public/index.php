<?php

require_once __DIR__ . '/../App/LicencaManager.php';

// Cabeçalhos para permitir acesso à API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Verifica a rota acessada
$rota = $_GET['rota'] ?? '';

// Rota: verificar_licenca
if ($rota === 'verificar_licenca') {
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    if (!isset($dados['licenca'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Licença não enviada']);
        exit;
    }

    try {
        $resultado = LicencaManager::gerarLicenca($dados['licenca']);
        echo json_encode($resultado);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Falha ao verificar licença', 'detalhe' => $e->getMessage()]);
    }

} else {
    // Rota padrão
    echo json_encode(['mensagem' => 'API Verão online!', 'versao' => '1.0']);
}
