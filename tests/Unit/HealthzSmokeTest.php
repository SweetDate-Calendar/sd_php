<?php

use SweetDate\Client;
use SweetDate\Config;

test('it GET /api/v1/healthz succeeds without auth', function () {

    $cfg = Config::fromEnv();
    $client = new Client($cfg);
    $resp = $client->request('GET', '/api/v1/healthz');

    expect($resp->getStatusCode())->toBe(200);
});
