<?php

declare(strict_types=1);

use SweetDate\Config;

it('loads config from env', function () {
    // capture old env to restore later
    $old = [
      'SWEETDATE_BASE_URL'   => getenv('SWEETDATE_BASE_URL') ?: null,
      'SWEETDATE_APP_ID'     => getenv('SWEETDATE_APP_ID') ?: null,
      'SWEETDATE_SK_B64URL'  => getenv('SWEETDATE_SK_B64URL') ?: null,
      'SWEETDATE_TIMEOUT_MS' => getenv('SWEETDATE_TIMEOUT_MS') ?: null,
    ];

    // set temp env
    putenv('SWEETDATE_BASE_URL=http://localhost:4008');
    putenv('SWEETDATE_APP_ID=app_test_123');
    putenv('SWEETDATE_SK_B64URL='); // empty => unsigned
    putenv('SWEETDATE_TIMEOUT_MS=15000');

    $cfg = Config::fromEnv();

    expect($cfg->baseUrl)->toBe('http://localhost:4008')
      ->and($cfg->appId)->toBe('app_test_123')
      ->and($cfg->skB64Url)->toBeNull()
      ->and($cfg->timeoutMs)->toBe(15000);

    // restore env
    foreach ($old as $k => $v) {
        putenv($v === null ? $k : "$k=$v");
    }
});
