<?php

/**
 * Global Pest configuration for this library.
 * Keep it minimal; no frameworks.
 */

uses()->in('.'); // only scan the tests/ folder

if (getenv('SWEETDATE_INTEGRATION') === '1') {
    require_once __DIR__ . '/Support/Integration.php';
}
