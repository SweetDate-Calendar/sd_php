<?php

declare(strict_types=1);

namespace SweetDate;

final class Config
{
    public function __construct(
        public string $baseUrl,
        public ?string $appId = null,
        public ?string $skB64Url = null,
        public int $timeoutMs = 10000,
        public ?\Closure $now = null
    ) {
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    public static function fromEnv(): self
    {
        $get = static function (string $key, ?string $fallback = null): ?string {
            $v = getenv($key);

            return ($v === false || $v === '') ? $fallback : $v;
        };

        $baseUrl   = $get('SWEETDATE_BASE_URL') ?? $get('SD_BASE_URL') ?? 'http://localhost:4001';
        $appId     = $get('SWEETDATE_APP_ID') ?? $get('SD_APP_ID');
        $skB64Url  = $get('SWEETDATE_SK_B64URL');
        $timeoutMs = (int) ($get('SWEETDATE_TIMEOUT_MS') ?? '10000');
        if ($timeoutMs <= 0) {
            $timeoutMs = 10000;
        }

        return new self(
            baseUrl: $baseUrl,
            appId: $appId,
            skB64Url: $skB64Url,
            timeoutMs: $timeoutMs,
            now: null // default to time()
        );
    }

    /** Current epoch seconds, overridable for tests. */
    public function now(): int
    {
        return $this->now ? ($this->now)() : \time();
    }
}
