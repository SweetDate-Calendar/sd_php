<?php

declare(strict_types=1);

namespace SweetDate;

final class Client
{
    public function __construct(
        private string $sweetDateBaseUrl,
        private string $appId,
        private string $secretSeedB64Url
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            getenv('SD_BASE_URL') ?: 'http://localhost:4008',
            getenv('SD_APP_ID') ?: '',
            getenv('SD_APP_SECRET') ?: ''
        );
    }

    /** Signed HTTP request (SignatureV1). */
    public function request(string $method, string $path, array $query = [], ?array $json = null): array
    {
        $methodU = strtoupper($method);
        $qs      = http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
        $pathQ   = $qs ? ($path . '?' . $qs) : $path;

        $ts = time();
        $canonical = "v1\n{$methodU}\n{$pathQ}\n{$ts}\n-";

        // ed25519 signature from base64url-encoded 32-byte seed
        $seed = $this->b64url_decode($this->secretSeedB64Url);
        $kp   = sodium_crypto_sign_seed_keypair($seed);
        $sk   = sodium_crypto_sign_secretkey($kp);
        $sigB = sodium_crypto_sign_detached($canonical, $sk);
        $sig  = $this->b64url_encode($sigB);

        $url = rtrim($this->sweetDateBaseUrl, '/') . $pathQ;
        $ch  = curl_init($url);

        $headers = [
          'sd-app-id: '    . $this->appId,
          'sd-timestamp: ' . $ts,
          'sd-signature: ' . $sig,
        ];
        if ($json !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json, JSON_UNESCAPED_SLASHES));
        }

        curl_setopt_array($ch, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST  => $methodU,
          CURLOPT_HTTPHEADER     => $headers,
        ]);

        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($body === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("HTTP error: $err");
        }
        curl_close($ch);

        $decoded = json_decode((string)$body, true);

        return is_array($decoded) ? $decoded + ['_http_status' => $status]
          : ['_http_status' => $status, 'raw' => $body];
    }

    private function b64url_encode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }
    private function b64url_decode(string $s): string
    {
        $p = strtr($s, '-_', '+/');
        $p .= str_repeat('=', (4 - strlen($p) % 4) % 4);
        $bin = base64_decode($p, true);
        if ($bin === false || strlen($bin) !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
            throw new \RuntimeException('SD_APP_SECRET must be base64url 32-byte ed25519 seed');
        }

        return $bin;
    }
}
