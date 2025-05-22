<?php

function  gerar_licenca($data){

if ($data) {
    $CAMINHO_CHAVE_PRIVADA = __DIR__ . '/../Keys/private.pem';
    $privateKey = file_get_contents($CAMINHO_CHAVE_PRIVADA);
    $privateKeyResource = openssl_pkey_get_private($privateKey);

    if (!$privateKeyResource) {
        die(json_encode([
            'mensagem' => 'Erro ao carregar a página.',
            'erro' => 'Licença gerada da forma errada.'
        ]));
    }

    $dadosJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $assinatura = null;

    if (!openssl_sign($dadosJson, $assinatura, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
        die(json_encode([
            'mensagem' => 'Erro ao gerar a licença.',
            'erro' => 'Falha na assinatura da licença.'
        ]));
    }

    $jsonFinal = [
        "dados" => $data,
        "assinatura" => base64_encode($assinatura)
    ];


    $jsonFinalString = json_encode($jsonFinal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (!$jsonFinalString) {
        echo json_encode(["erro" => "Licença com formato incorreto: " . json_last_error_msg()]);
        exit;
    }

    // Se for parceiro, verificar se já existe
    if (isset($data['tipo']) && $data['tipo'] === "parceiro") {
        $codigo = $data['codigo'] ?? null;
        $assinaturaCodificada = base64_encode($assinatura);
        $nomeParceiro = $data['nomeParceiro'] ?? "Desconhecido";
        $senha = $data['senha'] ?? "senha";

        $res = salvarParceiroNoJson($codigo, $assinaturaCodificada, $nomeParceiro,$senha);

       
                if ($res === 'duplicado') {
                    echo json_encode([
                        'erro' => 'Já existe um parceiro com este código.'
                    ]);
                    exit;
                }
    }

    $code = base64_encode($jsonFinalString);
    echo json_encode([
        'success' => $code
    ]);

} else {
    echo json_encode([
        'erro' => 'Dados ausentes ou formato inválido'
    ]);
}

}


function salvarParceiroNoJson($codigo, $assinatura, $nomeParceiro, $senha = null) {
    $arquivo = __DIR__ . '/parceiros.json';

    if (!file_exists($arquivo)) {
        file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT));
    }

    $conteudo = file_get_contents($arquivo);
    $dadosExistente = json_decode($conteudo, true);

    if (!is_array($dadosExistente)) {
        $dadosExistente = [];
    }

    // Atualiza senha se o código já existir mas ainda não tiver senha
    foreach ($dadosExistente as &$parceiro) {
        if ($parceiro['codigo'] === $codigo) {
            if (!isset($parceiro['senha']) && $senha !== null) {
                $parceiro['senha'] = password_hash($senha, PASSWORD_DEFAULT);
                file_put_contents($arquivo, json_encode($dadosExistente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return 'senha_adicionada';
            }
            return 'duplicado'; // Já existe e já tem senha
        }
    }

    // Novo cadastro
    $novoParceiro = [
        'codigo' => $codigo,
        'assinatura' => $assinatura,
        'nome_parceiro' => $nomeParceiro,
        'criado_em' => date('Y-m-d H:i:s')
    ];

    if ($senha !== null) {
        $novoParceiro['senha'] = password_hash($senha, PASSWORD_DEFAULT);
    }

    $dadosExistente[] = $novoParceiro;

    file_put_contents($arquivo, json_encode($dadosExistente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return 'ok';
}


function alterarSenhaParceiro($dados) {
    $arquivo = __DIR__ . '/parceiros.json';

    if (!file_exists($arquivo)) {
        return ['erro' => 'Base de dados de parceiros não encontrada.'];
    }

    $data = json_decode(file_get_contents($arquivo), true);
    
    foreach ($data as &$parceiro) {
        if ($parceiro['codigo'] == $dados["codigo"]) {
            // Verifica se a senha atual confere
            if (!password_verify($dados["senhaAtual"], $parceiro['senha'])) {
                return ['erro' => 'Senha atual incorreta.'];
            }

            // Altera a senha
            $parceiro['senha'] = password_hash($dados["novaSenha"], PASSWORD_DEFAULT);
            file_put_contents($arquivo, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return ['success' => 'Senha alterada com sucesso.'];
        }
    }

    return ['erro' => 'Código do parceiro não encontrado.'];
}


function loadParceiros() {
    $arquivo = __DIR__ . '/parceiros.json';

    if (!file_exists($arquivo)) {
        file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT));
    }

    $dados = json_decode(file_get_contents($arquivo), true);

    return $dados; // Retorna o array direto, o echo final é quem transforma em JSON
}
