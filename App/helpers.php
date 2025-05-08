<?php

function carregarChavePrivada()
{
    return file_get_contents(__DIR__ . '/../keys/private.pem');
}

function carregarChavePublica()
{
    return file_get_contents(__DIR__ . '/../keys/public.pem');
}
