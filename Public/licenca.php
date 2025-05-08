<?php

// licenca.php

// Recebe os dados enviados pela aplicação local (via POST)
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Exemplo de geração de chave com base nos dados (você pode melhorar isso com algoritmos mais seguros)
    $chave_gerada = md5(uniqid($data['id'], true));  // Gera uma chave simples (melhore conforme necessário)
var_dump($data);
    // Retorna a chave gerada para a aplicação local
    echo json_encode([
        'mensagem' => 'Licença gerada com sucesso!',
        'chave' => $chave_gerada
    ]);
} else {
    echo json_encode([
        'mensagem' => 'Erro ao receber os dados.'
    ]);
}
