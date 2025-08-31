<?php

declare(strict_types=1);

use SweetDate\Tenant;

require_once __DIR__ . '/../Support/Integration.php';

sd_prune_test_data();


test('Tenant::create creates a tenant and returns JSON', function () {
    // Make a unique name to avoid collisions on repeated runs

    $name = ci_tag('Tenant Create ' . bin2hex(random_bytes(4)));


    $out = Tenant::create($name);


    // Basic shape checks
    expect($out)
        ->toBeArray()
        ->toHaveKey('status', 'ok')
        ->toHaveKey('tenant');

    expect($out['tenant'])
        ->toBeArray()
        ->toHaveKeys(['id', 'name', 'created_at', 'updated_at']);

    // Echo back the name we sent
    expect($out['tenant']['name'])->toBe($name);
})
->group('integration');

afterAll(function () {
    sd_prune_test_data();
});
