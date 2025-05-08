<?php

class LicencaManager
{
    public static function gerarLicenca($dados)
    {
        $privateKey = file_get_contents(__DIR__ . '/../keys/private.pem');
        $private = openssl_pkey_get_private($privateKey);

        openssl_sign($dados, $assinatura, $private, OPENSSL_ALGO_SHA256);

        return base64_encode($assinatura);
    }

    public static function validarLicenca($dados, $assinatura)
    {
        $publicKey = file_get_contents(__DIR__ . '/../keys/public.pem');
        $public = openssl_pkey_get_public($publicKey);

        $assinatura = base64_decode($assinatura);
        return openssl_verify($dados, $assinatura, $public, OPENSSL_ALGO_SHA256) === 1;
    }
}
