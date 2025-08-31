<?php

declare(strict_types=1);

/**
 * Returns true when integration tests (real HTTP calls) should run.
 * Set SWEETDATE_INTEGRATION=1 to enable.
 */
function integrationEnabled(): bool
{
    return getenv('SWEETDATE_INTEGRATION') === '1';
}
