<?php
$data = $_POST['dados'] ?? '';
$assinatura = base64_decode($_POST['assinatura'] ?? '');

$publicKey = file_get_contents(__DIR__ . '/../keys/public.pem');
$public = openssl_pkey_get_public($publicKey);

$ok = openssl_verify($data, $assinatura, $public, OPENSSL_ALGO_SHA256);

echo json_encode(['valido' => $ok === 1]);
