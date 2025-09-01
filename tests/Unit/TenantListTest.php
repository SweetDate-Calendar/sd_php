<?php

declare(strict_types=1);

use SweetDate\Config;
use SweetDate\Tenant;

require_once __DIR__ . '/../Support/Integration.php';

sd_prune_test_data();


test('Tenant::list returns decoded JSON on 200', function () {

    $cfg    = new Config(baseUrl: 'http://example.test');

    $client = new SweetDate\Client($cfg);

    $out = Tenant::list(25, 0, $client);
    expect($out)
      ->toBeArray()
      ->and($out)->toHaveKey('status', 'ok')
      ->and($out)->toHaveKeys(['tenants', 'limit', 'offset'])
      ->and($out['tenants'])->toBeArray();
})->group('integration');

afterAll(function () {
    sd_prune_test_data();
});
