<?php

declare(strict_types=1);

namespace SweetDate\Exceptions;

use RuntimeException;

/**
 * Base API exception with HTTP context.
 */
class ApiException extends RuntimeException
{
    /** @var ?string */
    protected ?string $body = null;

    /** @var array<string, string|string[]> */
    protected array $headers = [];

    /**
     * @param array<string, string|string[]> $headers
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $body = null,
        array $headers = []
    ) {
        parent::__construct($message, $statusCode);
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    /** @return array<string, string|string[]> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
