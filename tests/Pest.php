<?php

/**
 * Global Pest configuration for this library.
 * Keep it minimal; no frameworks.
 */

// Discover tests under tests/
uses()->in('tests');

// Load integration helpers/hooks only for integration runs.
if (getenv('SWEETDATE_INTEGRATION') === '1') {
    require __DIR__ . '/Support/Integration.php';
}
