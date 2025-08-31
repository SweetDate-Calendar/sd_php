<?php

declare(strict_types=1);

namespace SweetDate;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SweetDate\Exceptions\ApiException;
use SweetDate\Exceptions\NotFound;
use SweetDate\Exceptions\ValidationError;

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
            'http_errors' => false,
        ]);
    }

    /**
     * Perform a request with automatic signing + error mapping.
     *
     * @param array<string,mixed> $options
     */
    public function request(string $method, string $path, array $options = []): ResponseInterface
    {
        if ($path === '' || $path[0] !== '/') {
            throw new ApiException('path must start with "/"');
        }

        $options['headers'] = $this->buildHeaders($method, $path, $options);

        try {
            $resp = $this->http->request(strtoupper($method), $path, $options);
        } catch (GuzzleException $e) {
            throw new ApiException('Transport error: ' . $e->getMessage());
        }

        return $this->mapErrors($resp);
    }

    /**
     * Merge existing + signature headers.
     *
     * @param array<string,mixed> $options
     * @return array<string,string>
     */
    private function buildHeaders(string $method, string $path, array $options): array
    {
        $headers = (array)($options['headers'] ?? []);

        return array_merge($headers, $this->signatureHeaders($method, $path));
    }

    /**
     * Always require credentials (fail fast).
     *
     * @return array<string,string>
     */
    private function signatureHeaders(string $method, string $path): array
    {
        if ($this->config->appId === null || $this->config->skB64Url === null) {
            throw new ApiException('Missing appId or secret key for request signing');
        }

        $ts = $this->config->now ? ($this->config->now)() : time();
        $canonical = $this->canonicalV1($method, $path, (string)$ts);
        $sig = $this->signB64UrlDetached($canonical, $this->config->skB64Url);

        return [
            'sd-app-id'    => $this->config->appId,
            'sd-timestamp' => (string)$ts,
            'sd-signature' => $sig,
        ];
    }

    private function canonicalV1(string $method, string $pathAndQuery, string $ts): string
    {
        return "v1\n" . strtoupper($method) . "\n{$pathAndQuery}\n{$ts}\n-";
    }

    /**
     * Map HTTP responses to domain exceptions.
     */
    private function mapErrors(ResponseInterface $resp): ResponseInterface
    {
        $code = $resp->getStatusCode();

        if ($code >= 200 && $code < 300) {
            return $resp;
        }

        $body    = $this->safeBody($resp);
        $headers = $this->safeHeaders($resp);

        return match ($code) {
            404 => throw new NotFound('not found', $body, $headers),
            422 => throw new ValidationError('validation failed', [], $body, $headers),
            default => throw new ApiException("http error {$code}", $code, $body, $headers),
        };
    }

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

        return base64_decode(strtr($b64url, '-_', '+/')) ?: '';
    }

    /** @return array<string,string|string[]> */
    private function safeHeaders(ResponseInterface $resp): array
    {
        return $resp->getHeaders();
    }

    private function safeBody(ResponseInterface $resp): ?string
    {
        try {
            return (string)$resp->getBody();
        } catch (\Throwable) {
            return null;
        }
    }
}
