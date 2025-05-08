<?php
$data = $_POST['dados'] ?? '';

$privateKey = file_get_contents(__DIR__ . '/../keys/private.pem');
$private = openssl_pkey_get_private($privateKey);

openssl_sign($data, $assinatura, $private, OPENSSL_ALGO_SHA256);

echo json_encode([
    'dados' => $data,
    'assinatura' => base64_encode($assinatura)
]);
