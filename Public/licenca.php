<?php

$data = json_decode(file_get_contents("php://input"), true);
header('Content-Type: application/json');



if (!$data || !isset($data['acao'])) {
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

require_once 'funcoes_parceiro.php'; // Todas as funções estão aqui

switch($data['acao']) {

    case 'alterar_senha':
        $ok = alterarSenhaParceiro($data);
        echo json_encode(['sucesso' => $ok]);
        exit;

    case 'carregar_dados_parceiros':
        $dados = loadParceiros();
        echo json_encode(['dados' => $dados]);
        exit;

    case 'gerar_licenca':

        return gerar_licenca($data);

        exit;


     case 'verificarSenhaCodigo':

        return verificarCodigoSenha($data->codigo,$data->senha);

        exit;

    
    default:
        echo json_encode(['erro' => 'Ação desconhecida']);
        exit;
}
