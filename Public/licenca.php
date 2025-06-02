<?php

$data = json_decode(file_get_contents("php://input"), true);
header('Content-Type: application/json; charset=utf-8');

if (!$data || !isset($data['acao'])) {
    echo json_encode(['error' => 'Dados inválidos ou ação não informada'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once 'funcoes_parceiro.php'; // Todas as funções refatoradas aqui

switch ($data['acao']) {

    case 'alterar_senha':
        $resultado = alterar_senha_parceiro($data);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;

    case 'carregar_dados_parceiros':
        $dados = carregar_registros();
        echo json_encode(['dados' => $dados], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;

    case 'gerar_licenca':
        $resultado = gerar_licenca($data);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;

    case 'gerar_codigo_parceiro':
        $resultado = gerar_codigo_parceiro($data);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;

    case 'verificar_senha_codigo':
        $resultado = verificar_codigo_senha($data);
        // Se resultado for string, interpretar como erro
        if (is_string($resultado)) {
            echo json_encode(['error' => $resultado], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        exit;

    default:
        echo json_encode(['error' => "Ação desconhecida: " . $data['acao']], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
}
