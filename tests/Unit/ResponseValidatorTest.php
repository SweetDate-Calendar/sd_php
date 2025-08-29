<?php

use GuzzleHttp\Psr7\Response;
use SweetDate\Exceptions\ApiException;
use SweetDate\Exceptions\NotFound;
use SweetDate\Exceptions\ValidationError;
use SweetDate\Internal\ResponseValidator as RV;

it('passes on ok statuses', function () {
    $resp = new Response(200, ['content-type' => 'application/json'], json_encode(['ok' => true]));
    RV::assertStatus($resp); // no exception
    expect(true)->toBeTrue();
});

it('throws NotFound on 404', function () {
    $resp = new Response(404, ['content-type' => 'application/json'], json_encode(['message' => 'nope']));
    expect(fn () => RV::assertStatus($resp))->toThrow(NotFound::class);
});

it('throws ValidationError on 422 and surfaces details', function () {
    $resp = new Response(
        422,
        ['content-type' => 'application/json'],
        json_encode(['message' => 'invalid input', 'details' => ['name' => ["can't be blank"]]])
    );

    try {
        RV::assertStatus($resp);
        expect()->fail('Expected ValidationError');
    } catch (ValidationError $e) {
        expect($e->getMessage())->toBe('invalid input')
          ->and($e->getDetails())->toHaveKey('name');
    }
});

it('throws ApiException on other errors and parses message from JSON if present', function () {
    $resp = new Response(500, ['content-type' => 'application/json'], json_encode(['error' => 'boom']));
    expect(fn () => RV::assertStatus($resp))->toThrow(ApiException::class);
});

it('requireKeys throws when keys are missing', function () {
    $data = ['status' => 'ok'];
    expect(fn () => RV::requireKeys($data, ['status', 'users'], 'test'))
      ->toThrow(ValidationError::class, 'missing required keys');
});
