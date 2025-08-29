<?php

declare(strict_types=1);

namespace SweetDate\Exceptions;

final class ValidationError extends ApiException
{
    /** @var array<string, mixed> */
    private array $details;




    /**
     * @param array<string, mixed> $details
     * @param array<string, string|string[]> $headers
     */
    public function __construct(
        string $message = 'validation failed',
        array $details = [],
        ?string $body = null,
        array $headers = []
    ) {
        // Always 422 for validation errors
        parent::__construct($message, 422, $body, $headers);
        $this->details = $details;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }
}
