<?php

// tools/print_pubkey.php
function b64url_encode(string $bin): string
{
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}
function b64url_decode(string $s): string
{
    $pad = strlen($s) % 4;
    if ($pad) {
        $s .= str_repeat('=', 4 - $pad);
    }

    return base64_decode(strtr($s, '-_', '+/'));
}

$seedB64url = getenv('SWEETDATE_SK_B64URL') ?: die("Missing SWEETDATE_SK_B64URL\n");
$seed = b64url_decode($seedB64url);
if (strlen($seed) !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
    die("Seed must be 32 bytes\n");
}

$kp = sodium_crypto_sign_seed_keypair($seed);
$pk = sodium_crypto_sign_publickey($kp);
echo 'app_id: '.(getenv('SWEETDATE_APP_ID') ?: '(unset)').PHP_EOL;
echo 'pubkey (base64url): '.b64url_encode($pk).PHP_EOL;
