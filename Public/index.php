<?php

function chamarApiLicenca($acao, $dados = [])
{
    //$apiUrl = "https://verao-api.onrender.com/licenca.php";
    $apiUrl = "http://localhost/Verao-Api/Public/licenca.php";

    $payload = json_encode(array_merge(['acao' => $acao], $dados));

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);

var_dump(  $response )   ;


if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['error' => 'Curl error: ' . $error_msg];
    }
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'Nenhuma resposta da API'];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        return ['error' => 'Resposta JSON inválida', 'raw' => $response];
    }

    return $decoded;
}
