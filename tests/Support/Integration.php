<?php

declare(strict_types=1);

use SweetDate\Client;
use SweetDate\Config;

function ci_seed(): string
{
    $seed = getenv('SWEETDATE_APP_ID');
    if (!$seed) {
        throw new RuntimeException('CI_SEED must be set for integration tests');
    }

    return $seed;
}

function ci_tag(string $name): string
{
    // Just append the seed
    return $name . '-' . ci_seed();
}


function sd_prune_test_data(): void
{
    $cfg    = Config::fromEnv();
    $client = new Client($cfg);

    try {
        $resp = $client->request('POST', '/api/v1/test/prune', [
            'json' => ['seed' => ci_seed()],
        ]);

        /** @var \Psr\Http\Message\ResponseInterface $resp */

        \SweetDate\Internal\ResponseValidator::assertStatus($resp, [200]);
    } catch (\Throwable $e) {
        print_r('catch');
        // ignore if endpoint not available
    }
}
