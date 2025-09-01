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
        return new self(
            baseUrl: getenv('SWEETDATE_BASE_URL') ?: 'http://localhost:4008',
            appId: getenv('SWEETDATE_APP_ID') ?: '',
            skB64Url: getenv('SWEETDATE_SK_B64URL') ?: '',
            timeoutMs: (int) getenv('SWEETDATE_TIMEOUT_MS') ?: 10000,
            now: null
        );
    }

    /** Current epoch seconds, overridable for tests. */
    public function now(): int
    {
        return $this->now ? ($this->now)() : \time();
    }
}
