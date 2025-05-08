<?php

// Recebe os dados enviados pela aplicação local (via POST)
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Carregar chave privada
    $CAMINHO_CHAVE_PRIVADA = "../Keys/private.pem";  // Substitua pelo caminho correto da sua chave privada
    $privateKey = file_get_contents($CAMINHO_CHAVE_PRIVADA);
    
    // Verifica se a chave privada foi carregada corretamente
    $privateKeyResource = openssl_pkey_get_private($privateKey);
    if (!$privateKeyResource) {
        die(json_encode([
            'mensagem' => 'Erro ao carregar chave privada.',
            'erro' => 'A chave privada não pôde ser carregada corretamente.'
        ]));
    }
    
    // Gerar assinatura dos dados com a chave privada
    $dadosJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $assinatura = null;
    if (!openssl_sign($dadosJson, $assinatura, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
        die(json_encode([
            'mensagem' => 'Erro ao assinar os dados.',
            'erro' => 'Falha na assinatura dos dados.'
        ]));
    }
    
    // Codificar a assinatura em base64
    $assinaturaBase64 = base64_encode($assinatura);

    // Retornar os dados assinados (você pode gerar um arquivo ou apenas retornar os dados para o cliente)
    echo json_encode([
        'mensagem' => 'Licença gerada com sucesso!',
        'dados' => $data,
        'assinatura' => $assinaturaBase64
    ]);
} else {
    echo json_encode([
        'mensagem' => 'Erro ao receber os dados.',
        'erro' => 'Dados ausentes ou formato inválido'
    ]);
}

