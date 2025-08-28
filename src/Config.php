<?php

namespace SweetDate;

final class Config
{
  public function __construct(
    public string $baseUrl,
    public ?string $apiKey = null,
    public int $timeoutMs = 10000
  ) {}

  public static function fromEnv(): self
  {
    return new self(
      baseUrl: getenv('SWEETDATE_BASE_URL') ?: 'http://localhost:4000/api/v1',
      apiKey: getenv('SWEETDATE_API_KEY') ?: null,
      timeoutMs: (int) (getenv('SWEETDATE_TIMEOUT_MS') ?: 10000)
    );
  }
}
