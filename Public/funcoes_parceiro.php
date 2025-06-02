<?php 

// Define a pasta onde ficam os arquivos JSON de cada parceiro
define('PARCEIROSLICENCAS', __DIR__ . '/../Keys/parceiros_licencas/');

include_once "funcoesExtras.php"; // Inclui funções auxiliares (não mostradas aqui)

/**
 * Função para gerar ou atualizar o código (dados) do parceiro
 * Recebe um array $data com os dados do parceiro (codigo, senha, limite, preco, confirmação, etc)
 * Retorna um array com status ou erro
 */
function gerar_codigo_parceiro(array $data): array {
    // Valida os dados obrigatórios do parceiro
    $erro = validar_dados_parceiro($data);
    if ($erro) return ['error' => $erro];

    $confirmacao = base64_decode($data['confirmacao'] ?? '');
    if (!in_array($confirmacao, ['confirmadoveraoParceiro', 'confirmadoveraoNormal'], true)) {
        return ['warning' => 'Confirmação inválida'];
    }

    $codigo = trim($data['codigo'] ?? '');
    $senha = $data['senha'] ?? '';
    if (empty($codigo) || empty($senha)) {
        return ['error' => 'Código e senha são obrigatórios.'];
    }

    $tipo = $confirmacao === 'confirmadoveraoParceiro' ? 'parceiro' : 'cliente';

    // Caminho onde o arquivo será salvo
    $filePath = PARCEIROSLICENCAS . "parceiro_" . $codigo . ".json";

    // Verifica se o arquivo já existe e impede duplicação
    if (!file_exists($filePath)) {
        $status = 'sucesso';
    } else {
        // O parceiro já existe, então é duplicado
        return ['warning' => 'Já existe um parceiro com este código.'];
    }

    // Prepara dados para gerar assinatura digital
    $dadosParaAssinar = [
        'codigo' => $codigo,
        'confirmacao' => $data['confirmacao']
    ];

    $dadosAssinados = gerar_licenca_base($dadosParaAssinar);
    if (!$dadosAssinados || empty($dadosAssinados['assinatura'])) {
        return ['error' => 'Erro ao gerar assinatura digital.'];
    }

    $dadosParceiro = [
        'tipo' => $tipo,
        'codigo' => $codigo,
        'preco' => $data['preco'] ?? '',
        'limite' => (int)($data['limite'] ?? 0),
        'confirmacao' => $data['confirmacao'],
        'senha' => password_hash($senha, PASSWORD_DEFAULT),
        'usadas' => 0,
        'assinatura' => $dadosAssinados['assinatura']
    ];

    $conteudo = ['dados' => $dadosParceiro, 'licencas' => []];

    // Salva o JSON formatado no arquivo parceiro_xxx.json
    file_put_contents($filePath, json_encode($conteudo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return ['status' => $status, 'mensagem' => 'Parceiro salvo.', 'codigo' => $codigo];
}

/**
 * Função para alterar a senha de um parceiro já existente
 * Recebe array com código, senhaAtual, novaSenha
 * Retorna sucesso ou erro
 */
function alterar_senha_parceiro(array $dados): array {
    $codigo = trim($dados['codigo']);
    $filePath = PARCEIROSLICENCAS . "parceiro_" . $codigo . ".json";

    if (!file_exists($filePath)) return ['error' => 'Parceiro não encontrado.'];

    // Carrega arquivo JSON do parceiro
    $json = json_decode(file_get_contents($filePath), true);

    // Verifica se a senha atual informada bate com a senha armazenada
    if (!password_verify($dados["senhaAtual"], $json['dados']['senha'])) {
        return ['error' => 'Senha atual incorreta.'];
    }

    // Atualiza a senha com hash
    $json['dados']['senha'] = password_hash($dados["novaSenha"], PASSWORD_DEFAULT);

    // Salva o arquivo JSON atualizado
    file_put_contents($filePath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return ['success' => 'Senha alterada com sucesso.'];
}

/**
 * Função para gerar uma nova licença para um parceiro
 * Recebe os dados da licença (incluindo codigo_parceiro, demo_definitiva, etc)
 * Retorna a licença codificada ou erro
 */
function gerar_licenca(array $data): array {
    if (empty($data)) {
        return ['error' => 'Dados ausentes ou formato inválido.'];
    }

    $codigo = trim($data['codigo_parceiro']);
    $filePath = PARCEIROSLICENCAS . "parceiro_" . $codigo . ".json";

    // Verifica se arquivo do parceiro existe
    if (!file_exists($filePath)) return ['error' => 'Parceiro não encontrado.'];

    // Carrega os dados JSON do parceiro
    $json = json_decode(file_get_contents($filePath), true);

    // Verifica se a licença é para uso total (não Demo)
    $ehUsoTotal = ($data['demo_definitiva'] != "Demo");

    // Se uso total, verifica se limite de licenças foi atingido
    if ($ehUsoTotal && $json['dados']['usadas'] >= $json['dados']['limite']) {
        return ['warning' => 'Limite de licenças atingido.'];
    }

    // Gera a licença base assinada digitalmente (função externa)
    $assinado = gerar_licenca_base($data);
    if (!$assinado) return ['error' => 'Falha ao gerar a licença.'];

    // Codifica licença para base64 JSON formatado
    $code = base64_encode(json_encode($assinado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

    // Cria array da licença para salvar no JSON do parceiro
    $licenca = [
        'data_geracao' => date('Y-m-d H:i:s'),
        'tipo' => $data['demo_definitiva'],
        'licenca_base64' => $code
    ];

    // Adiciona essa licença na lista de licenças do parceiro
    $json['licencas'][] = $licenca;

    // Se for licença uso total, incrementa o contador de usadas
    if ($ehUsoTotal) $json['dados']['usadas']++;

    // Salva o arquivo JSON atualizado
    file_put_contents($filePath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Retorna a licença codificada para uso
    return ['success' => $code];
}
