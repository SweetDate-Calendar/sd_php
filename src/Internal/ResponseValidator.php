<?php

declare(strict_types=1);

namespace SweetDate\Internal;

use Psr\Http\Message\ResponseInterface;
use SweetDate\Exceptions\ApiException;
use SweetDate\Exceptions\NotFound;
use SweetDate\Exceptions\ValidationError;

/**
 * Centralized HTTP response checks + minimal JSON validation.
 */
final class ResponseValidator
{
    /**
     * Assert HTTP status is one of the allowed codes.
     * Throws mapped exceptions; tries to parse JSON details if present.
     *
     * @param int[] $okStatuses
     */
    public static function assertStatus(ResponseInterface $resp, array $okStatuses = [200, 201, 202, 204]): void
    {
        $code = $resp->getStatusCode();
        if (in_array($code, $okStatuses, true)) {
            return;
        }

        [$body, $headers] = [self::safeBody($resp), self::safeHeaders($resp)];
        /** @var array<string,mixed>|null $json */
        $json = self::maybeJson($resp);

        // Best-effort message
        /** @var string|null $message */
        $message = null;
        if (is_array($json)) {
            $msg = $json['message'] ?? ($json['error'] ?? null);
            if (is_string($msg)) {
                $message = $msg;
            }
        }
        $message ??= 'http error ' . $code;

        if ($code === 404) {
            throw new NotFound($message, $body, $headers);
        }

        if ($code === 422) {
            /** @var array<string,mixed> $details */
            $details = [];
            if (is_array($json) && isset($json['details']) && is_array($json['details'])) {
                /** @var array<string,mixed> $asArray */
                $asArray = $json['details'];
                $details = $asArray;
            }
            throw new ValidationError($message, $details, $body, $headers);
        }

        throw new ApiException($message, $code, $body, $headers);
    }

    /**
     * Ensure required keys exist in a decoded JSON map.
     *
     * @param array<string,mixed> $data
     * @param string[]            $keys
     */
    public static function requireKeys(array $data, array $keys, string $context = 'response'): void
    {
        $missing = [];
        foreach ($keys as $k) {
            if (!array_key_exists($k, $data)) {
                $missing[] = $k;
            }
        }
        if ($missing !== []) {
            $details = ['missing' => $missing, 'context' => $context];
            throw new ValidationError('missing required keys', $details);
        }
    }

    /** @return array<string,string|string[]> */
    private static function safeHeaders(ResponseInterface $resp): array
    {
        return $resp->getHeaders();
    }

    private static function safeBody(ResponseInterface $resp): ?string
    {
        try {
            return (string) $resp->getBody();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Decode JSON if content-type indicates JSON; otherwise null.
     *
     * @return array<string,mixed>|null
     */
    private static function maybeJson(ResponseInterface $resp): ?array
    {
        $ctype = implode(', ', $resp->getHeader('content-type'));
        if ($ctype === '' || stripos($ctype, 'application/json') === false) {
            return null;
        }
        $raw = self::safeBody($resp);
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }
}
