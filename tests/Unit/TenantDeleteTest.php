<?php

declare(strict_types=1);

use SweetDate\Exceptions\NotFound;
use SweetDate\Tenant;

require_once __DIR__ . '/../Support/Integration.php';

sd_prune_test_data();


// Run with: composer test:int
test('Tenant::delete deletes tenant and returns JSON', function () {
    // First create a tenant we can delete
    $created = Tenant::create('CI Delete ' . bin2hex(random_bytes(4)));
    $id      = $created['tenant']['id'];

    // Perform the delete
    $out = Tenant::delete($id);

    // Basic shape assertions
    expect($out)
        ->toBeArray()
        ->and($out)->toHaveKey('status', 'ok')
        ->and($out)->toHaveKey('tenant')
        ->and($out['tenant'])->toHaveKey('id', $id);

    // Optional confirmation: follow-up GET should be 404
    expect(fn () => Tenant::get($id))->toThrow(NotFound::class);
})->group('integration');

// Run with: composer test:int
test('Tenant::delete maps 404 to NotFound', function () {
    expect(fn () => Tenant::delete('00000000-0000-0000-0000-000000000000'))
        ->toThrow(NotFound::class);
})->group('integration');
