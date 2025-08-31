<?php

declare(strict_types=1);

use SweetDate\Exceptions\NotFound;
use SweetDate\Tenant;

require_once __DIR__ . '/../Support/Integration.php';

sd_prune_test_data();


test('Tenant::get returns tenant json on 200', function () {


    $name = ci_tag('Tenant Get ' . bin2hex(random_bytes(4)));


    $created = Tenant::create($name);
    $id = $created['tenant']['id'];
    print_r($id);

    $out = Tenant::get($id);


    expect($out)
        ->toBeArray()
        ->and($out)->toHaveKey('status', 'ok')
        ->and($out)->toHaveKey('tenant')
        ->and($out['tenant'])->toHaveKey('id', $id)
        ->and($out['tenant'])->toHaveKey('name');
})->group('integration');

test('Tenant::get maps 404 to NotFound', function () {
    expect(fn () => Tenant::get('ffffffff-ffff-ffff-ffff-ffffffffffff'))
        ->toThrow(NotFound::class);
})->group('integration');
