<?php

declare(strict_types=1);

use SweetDate\Client;
use SweetDate\Config;

it('GET /api/v1/healthz succeeds without auth', function () {
  // Ensure no signing headers get added: do NOT set app id or key.
  putenv('SWEETDATE_BASE_URL=http://localhost:4008');
  putenv('SWEETDATE_APP_ID');
  putenv('SWEETDATE_SK_B64URL');

  $cfg = Config::fromEnv();
  $client = new Client($cfg);

  $resp = $client->request('GET', '/api/v1/healthz');

  expect($resp->getStatusCode())->toBeGreaterThanOrEqual(200)
    ->and($resp->getStatusCode())->toBeLessThan(300);
});
