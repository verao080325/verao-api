<?php

// Recebe os dados enviados pela aplicação local (via POST)
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Carregar chave privada
    $CAMINHO_CHAVE_PRIVADA = __DIR__ . '/../Keys/private.pem';
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

    // Retornar os dados assinados (você pode gerar um arquivo ou apenas retornar os dados para o cliente)
    $jsonFinal = [
        "dados" => $dados,
        "assinatura" => base64_encode($assinatura)
    ];
    
    

$jsonFinal = json_encode($jsonFinal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (!$jsonFinal) {
    echo "Erro ao gerar JSON: " . json_last_error_msg();
    exit;
}

$code = base64_encode($jsonFinal);
echo json_encode([
        
    'success' => $code
]);
} else {
    echo json_encode([
        
        'error' => 'Dados ausentes ou formato inválido'
    ]);
}

