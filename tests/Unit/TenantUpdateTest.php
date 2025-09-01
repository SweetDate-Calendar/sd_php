<?php

declare(strict_types=1);

use SweetDate\Exceptions\NotFound;
use SweetDate\Exceptions\ValidationError;
use SweetDate\Tenant;

require_once __DIR__ . '/../Support/Integration.php';

sd_prune_test_data();


// Run with: composer test:int
test('Tenant::update updates the name and returns JSON', function () {

    $name = ci_tag('Tenant Update ' . bin2hex(random_bytes(4)));
    $created = Tenant::create($name);
    $id = $created['tenant']['id'];

    $newName = ci_tag('Tenant Updated ' . bin2hex(random_bytes(4)));

    $out = Tenant::update($id, $newName);

    expect($out)
      ->toBeArray()
      ->and($out)->toHaveKey('status', 'ok')
      ->and($out)->toHaveKey('tenant');

    expect($out['tenant'])
      ->toHaveKeys(['id', 'name', 'created_at', 'updated_at'])
      ->and($out['tenant']['id'])->toBe($id)
      ->and($out['tenant']['name'])->toBe($newName);

})->group('integration');

// Run with: composer test:int
test('Tenant::update maps 404 to NotFound', function () {
    expect(fn () => Tenant::update('ffffffff-ffff-ffff-ffff-ffffffffffff', 'Nope'))
        ->toThrow(NotFound::class);
})->group('integration');

// Run with: composer test:int
test('Tenant::update maps 422 to ValidationError on blank name', function () {
    // arrange: create a tenant to update


    $name = ci_tag('Tenant Create ' . bin2hex(random_bytes(4)));


    $created = Tenant::create($name);
    $id      = $created['tenant']['id'];

    // act + assert
    expect(fn () => Tenant::update($id, ''))
        ->toThrow(ValidationError::class);
})->group('integration');

afterEach(function () {
    sd_prune_test_data();
});
