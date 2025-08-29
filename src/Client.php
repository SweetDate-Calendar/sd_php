<?php

declare(strict_types=1);

namespace SweetDate;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SweetDate\Exceptions\ApiException;
use SweetDate\Exceptions\NotFound;
use SweetDate\Exceptions\ValidationError;

/**
 * Minimal HTTP client with SignatureV1 support and basic retry.
 */
final class Client
{
    private Config $config;
    private GuzzleClient $http;

    public function __construct(Config $config, ?GuzzleClient $http = null)
    {
        $this->config = $config;
        $this->http = $http ?? new GuzzleClient([
          'base_uri' => $this->config->baseUrl,
          'timeout'  => $this->config->timeoutMs / 1000.0,
        ]);
    }

    /**
     * Perform a request against a path (must begin with "/").
     * Adds SweetDate SignatureV1 headers if appId + key present.
     *
     * @param array<string,mixed> $options  Guzzle options (json, query, headers, etc.)
     */
    public function request(string $method, string $pathWithLeadingSlash, array $options = []): ResponseInterface
    {
        $method = strtoupper($method);
        if ($pathWithLeadingSlash === '' || $pathWithLeadingSlash[0] !== '/') {
            // Keep it strict to avoid canonicalization mistakes.
            throw new ApiException('path must start with "/"');
        }

        // Merge headers with signature (if available)
        $headers = (array)($options['headers'] ?? []);
        $sigHeaders = $this->signatureHeadersOrEmpty($method, $pathWithLeadingSlash);
        $options['headers'] = array_merge($headers, $sigHeaders);

        /** @var ResponseInterface|null $resp */
        $resp = null;

        // Basic retry/backoff on transport errors only; server errors are mapped below.
        $attempts    = 0;
        $maxAttempts = 3;
        $delayMs     = 150;

        while ($attempts < $maxAttempts) {
            try {
                /** @var ResponseInterface $tmp */
                $tmp = $this->http->request($method, $pathWithLeadingSlash, $options);
                $resp = $tmp;
                break; // success
            } catch (GuzzleException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw new ApiException($e->getMessage());
                }
                usleep($delayMs * 1000);
                $delayMs *= 2;
            }
        }

        if ($resp === null) {
            // Defensive: should not happen (we either broke or threw above).
            throw new ApiException('No response received');
        }

        $code = $resp->getStatusCode();

        if ($code >= 200 && $code < 300) {
            return $resp;
        }

        // Error mapping (best-effort). Not parsing JSON yet.
        if ($code === 404) {
            throw new NotFound('not found', $this->safeBody($resp), $this->safeHeaders($resp));
        }

        if ($code === 422) {
            throw new ValidationError(
                'validation failed',
                [], // details
                $this->safeBody($resp),
                $this->safeHeaders($resp)
            );
        }

        // Other non-2xx
        throw new ApiException(
            'http error ' . $code,
            $code,
            $this->safeBody($resp),
            $this->safeHeaders($resp)
        );
    }

    /**
     * Build SignatureV1 headers if we have appId and a signing key; otherwise return [].
     *
     * @return array<string,string>
     */
    private function signatureHeadersOrEmpty(string $method, string $pathAndQuery): array
    {
        if ($this->config->appId === null || $this->config->skB64Url === null) {
            return []; // unsigned (ok for /healthz and local smoke)
        }

        $ts = $this->config->now ? ($this->config->now)() : time();
        $canonical = $this->canonicalV1($method, $pathAndQuery, (string)$ts);
        $sig = $this->signB64UrlDetached($canonical, $this->config->skB64Url);

        return [
          'sd-app-id'    => $this->config->appId,
          'sd-timestamp' => (string)$ts,
          'sd-signature' => $sig,
        ];
    }

    private function canonicalV1(string $method, string $pathAndQuery, string $ts): string
    {
        $methodUp = strtoupper($method);

        return "v1\n{$methodUp}\n{$pathAndQuery}\n{$ts}\n-\n";
    }

    /**
     * Sign using libsodium (Ed25519) where env contains a 32-byte seed in base64url (no padding).
     */
    private function signB64UrlDetached(string $msg, string $seedB64Url): string
    {
        $seed = $this->b64urlDecode($seedB64Url);
        if (strlen($seed) !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
            throw new ApiException('invalid secret seed length for Ed25519');
        }

        $keypair = sodium_crypto_sign_seed_keypair($seed);
        $secret  = sodium_crypto_sign_secretkey($keypair);
        $sig     = sodium_crypto_sign_detached($msg, $secret);

        return $this->b64urlEncode($sig);
    }

    private function b64urlEncode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $b64url): string
    {
        $pad = strlen($b64url) % 4;
        if ($pad !== 0) {
            $b64url .= str_repeat('=', 4 - $pad);
        }
        /** @var string $out */
        $out = base64_decode(strtr($b64url, '-_', '+/')) ?: '';

        return $out;
    }

    /** @return array<string,string|string[]> */
    private function safeHeaders(ResponseInterface $resp): array
    {
        return $resp->getHeaders();
    }

    /** @return string|null */
    private function safeBody(ResponseInterface $resp): ?string
    {
        try {
            return (string)$resp->getBody();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
