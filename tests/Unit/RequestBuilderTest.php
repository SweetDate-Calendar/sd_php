<?php

use SweetDate\Internal\RequestBuilder as RB;

it('builds colon and brace paths and preserves leading slash', function () {
    expect(RB::path('/api/v1/tenants/:id', ['id' => 't-1']))->toBe('/api/v1/tenants/t-1');
    expect(RB::path('api/v1/users/{uid}', ['uid' => 'u 2']))->toBe('/api/v1/users/u%202'); // encodes
});

it('appends query with RFC3986 encoding', function () {
    $p = RB::withQuery('/api/v1/users', ['q' => 'A&B', 'limit' => 25, 'offset' => 0]);
    expect($p)->toBe('/api/v1/users?q=A%26B&limit=25&offset=0');

    // appending to an existing ? keeps &
    $p2 = RB::withQuery('/api/v1/users?limit=10', ['offset' => 10]);
    expect($p2)->toBe('/api/v1/users?limit=10&offset=10');
});
