<?php

declare(strict_types=1);

namespace SweetDate\Exceptions;

/**
 * 404 Not Found error.
 */
final class NotFound extends ApiException
{
    /**
     * @param array<string,string|string[]> $headers
     */
    public function __construct(string $message = 'not found', ?string $body = null, array $headers = [])
    {
        parent::__construct($message, 404, $body, $headers);
    }
}
