<?php






function chamarApiLicenca($acao, $dados=null) {
$url = 'http://localhost/Verao-Api/Public/licenca.php'; // Usa URL HTTP e não __DIR__

    $payload = json_encode(array_merge(['acao' => $acao], $dados));

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    $resposta = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erro cURL: ' . curl_error($ch);
}

  
    return json_decode($resposta, true);



}

$dados = [
    'codigo' => 'P001',
    'senhaAtual' => 'senha_antiga123',
    'novaSenha' => 'nova_senha456'
];

$resposta = chamarApiLicenca('gerar_licenca', $dados);
$load = chamarApiLicenca('carregar_dados_parceiros', $dados);
$alterar_senha = chamarApiLicenca('alterar_senha', $dados);

    var_dump($alterar_senha['sucesso']['erro']);
/*
if (isset($resposta['success'])) {
    $licenca = base64_decode($resposta['success']);
    var_dump($licenca); // Isso agora deve funcionar corretamente
} else {
    echo "Erro: resposta inesperada\n";
    var_dump($resposta);
}


/*
if (isset($resposta['success'])) {
    echo "Licença: " . $resposta['success'];
} elseif (isset($resposta['erro'])) {
    echo "Erro: " . $resposta['erro'];
} else {
    echo "Erro desconhecido.";
}

*/