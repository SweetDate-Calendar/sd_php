<?php

/**
 * Global Pest configuration for this library.
 * Keep it minimal; no Laravel TestCase, no app container, etc.
 */

// Tell Pest to discover tests in the root tests/ folder.
// You can split into 'Unit' and 'Integration' folders later if you like.
uses()->in('.');
