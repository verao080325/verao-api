<?php 

/**
 * Envia uma resposta JSON para o cliente
 */
function responder(array $mensagem): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($mensagem, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}
 
/**
 * Envia uma resposta de erro em formato JSON
 */
function responder_erro(string $mensagem): void {
    responder(['error' => $mensagem]);
}

/**
 * Carrega os registros existentes do arquivo JSON (dados dos parceiros e licenças)
 */
function carregar_registros(): array {
    // Garante que o diretório de parceiros exista
    if (!is_dir(PARCEIROSLICENCAS)) {
        mkdir(PARCEIROSLICENCAS, 0755, true);
    }

    $registros = [];

    // Lê todos os arquivos JSON na pasta de parceiros
    foreach (glob(PARCEIROSLICENCAS . "parceiro_*.json") as $file) {
        try {
            $conteudo = file_get_contents($file);
            if (trim($conteudo) === '') continue; // ignora se o arquivo estiver vazio

            $dados = json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR);
            $registros[] = $dados;
        } catch (JsonException $e) {
            // Se algum arquivo estiver malformado, apenas ignora
            continue;
        }
    }

    return $registros;
}


/**
 * Salva os registros no arquivo JSON
 */
function salvar_registros(array $dados): bool {
    return file_put_contents(PARCEIROSLICENCAS, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Valida os dados enviados pelo formulário de criação de parceiro
 */
function validar_dados_parceiro(array $data): ?string {
    if (empty($data['codigo']) || !preg_match('/^[a-zA-Z0-9_\-]+$/', $data['codigo'])) {
        return 'Código inválido.';
    }
    if (!isset($data['preco']) || !is_numeric($data['preco'])) {
        return 'Preço inválido.';
    }
    if (!isset($data['limite']) || !is_numeric($data['limite']) || $data['limite'] < 1) {
        return 'Limite inválido.';
    }
    if (empty($data['confirmacao'])) {
        return 'Confirmação obrigatória.';
    }
    return null;
}

/* echo json_encode([
    "success" => ["Importado com sucesso!"],
    "warning" => ["Produto X já existe."],
    "error" => [],
    "delet" => []
]);

 * Gera uma assinatura digital (licença base) com chave privada RSA
 */
function gerar_licenca_base(array $data): array|false {
    $chave = file_get_contents(__DIR__ . '/../Keys/private.pem');
    $res = openssl_pkey_get_private($chave);
    if (!$res) return false;

    $dados = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $assinatura = null;
    if (!openssl_sign($dados, $assinatura, $res, OPENSSL_ALGO_SHA256)) return false;

    return ["dados" => $data, "assinatura" => base64_encode($assinatura)];
}




function verificar_codigo_senha(array $data): string|array {
    $registros = carregar_registros();

    foreach ($registros as $parceiro) {
        if (($parceiro['dados']['codigo'] ?? '') === ($data['codigo'] ?? '')) {
            if (!isset($parceiro['dados']['senha'])) return 'senha_nao_definida';
            
            return password_verify($data['senha'], $parceiro['dados']['senha'])
                ? $parceiro
                : 'senha_incorreta';
        }
    }

    return 'codigo_nao_encontrado';
}






function salvar_licenca_json(array $licenca)
{

    // Lê as licenças existentes ou cria array vazio
    $dados = file_exists(PARCEIROSLICENCAS) ? json_decode(file_get_contents(PARCEIROSLICENCAS), true) : [];

    // Adiciona nova licença ao array
    $dados[] = $licenca;

    // Salva novamente no arquivo
    file_put_contents(PARCEIROSLICENCAS, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}



function verificar_limite_licencas(string $codigo): bool {
    $registros = carregar_registros();

    
    foreach ($registros as $reg) {
        if (trim($reg['codigo']) === trim($codigo)) {
            $usadas = isset($reg['usadas']) ? (int)$reg['usadas'] : 0;
            $limite = isset($reg['limite']) ? (int)$reg['limite'] : 0;
            return $usadas < $limite;
        }
    }
    // Se não existe ainda, o limite não foi excedido
    return true;
}

function incrementar_licenca_usada(string $codigo): bool {
    $registros = carregar_registros();

    foreach ($registros as &$reg) {
        if (trim($reg['codigo']) === trim($codigo)) {
            $reg['usadas'] = isset($reg['usadas']) ? ((int)$reg['usadas']) + 1 : 1;
            return salvar_registros($registros);
        }
    }

    return false; // código não encontrado
}
