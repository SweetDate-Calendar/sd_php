<?php

require __DIR__ . '/../vendor/autoload.php';

use SweetDate\Config;
use SweetDate\Client;

// Load config from environment (.env, .mise.toml, or export in shell)
$cfg = Config::fromEnv();

// Create client
$cli = new Client($cfg);

// Health check (no auth needed)
$health = $cli->get('/api/v1/healthz');
echo "Healthz:\n";
print_r($health);

// Signed request example
try {
  $whoami = $cli->get('/api/v1/whoami');
  echo "Whoami:\n";
  print_r($whoami);
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
