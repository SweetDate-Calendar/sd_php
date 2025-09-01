<?php

declare(strict_types=1);

namespace SweetDate;

use SweetDate\Exceptions\ApiException;
use SweetDate\Internal\RequestBuilder;
use SweetDate\Internal\ResponseValidator;

/**
 * @phpstan-type TenantJson array{
 *   id: string,
 *   name: string,
 *   created_at?: string,
 *   updated_at?: string,
 *   inserted_at?: string
 * }
 * @phpstan-type GetResponse array{
 *   status: string,
 *   tenant: TenantJson
 * }
 */


final class Tenant
{
    /**
     * Returns tenants ordered by name ascending.
     * Command: TENANTS.GET_LIST
     * endpoint /api/v1/tenants
     *
     * @param int $limit  Maximum number of items to return. (default 25, min 1, max 100)
     * @param int $offset  Number of items to skip. (default 0, min 0)
     *
     * Example response:
     *   {
     *     "result": {
     *       "limit": 25,
     *       "offset": 0,
     *       "tenants": [
     *         {},
     *         {}
     *       ]
     *     },
     *     "status": "ok"
     *   }
     *
     * Error response:
     *   {
     *     "message": "not found",
     *     "status": "error"
     *   }
     *
     * @return array<string,mixed>
     */
    public static function list(int $limit = 25, int $offset = 0): array
    {
        $cfg    = Config::fromEnv();
        $client = new Client($cfg);

        $path = RequestBuilder::path('/api/v1/tenants');
        $path = RequestBuilder::withQuery($path, [
          'limit'  => $limit,
          'offset' => $offset,
        ]);

        $resp = $client->request('GET', $path);

        ResponseValidator::assertStatus($resp, [200]);

        $raw  = (string) $resp->getBody();
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new ApiException('invalid JSON from GET /tenants', 500, $raw, $resp->getHeaders());
        }

        ResponseValidator::requireKeys($json, ['status'], 'tenants.list');

        return $json;
    }


    /**
     * Create a new tenant.
     * Command: TENANTS.CREATE
     * endpoint /api/v1/tenants
     *
     * @param string $name
     *
     * Example response:
     *   {
     *     "status": "ok",
     *     "tenant": {
     *       "id": "00000000-0000-0000-0000-000000000000",
     *       "inserted_at": "2025-08-18T09:20:00Z",
     *       "name": "Building 4",
     *       "updated_at": "2025-08-19T10:15:00Z"
     *     }
     *   }
     *
     * Error response:
     *   {
     *     "details": {
     *       "name": [
     *         "can't be blank"
     *       ]
     *     },
     *     "message": "validation failed",
     *     "status": "error"
     *   }
     *
     * @return array<string,mixed>
     */
    public static function create(string $name): array
    {
        $cfg    = Config::fromEnv();
        $client = new Client($cfg);

        $path = '/api/v1/tenants';

        $resp = $client->request('POST', $path, [
            'json' => ['name' => $name],
        ]);

        ResponseValidator::assertStatus($resp, [200, 201]);

        /** @var \Psr\Http\Message\ResponseInterface $resp */

        $raw  = (string) $resp->getBody();
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new ApiException('invalid JSON from POST /tenants', 500, $raw, $resp->getHeaders());
        }

        ResponseValidator::requireKeys($json, ['status', 'tenant'], 'tenants.create');


        /** @var array $json */
        /** @phpstan-var GetResponse $json */


        return $json;
    }


    /**
     * Get a single tenant by ID.
     * Command: TENANTS.GET
     * Endpoint: /api/v1/tenants/:id
     *
     *
     * Example response:
     * {
     *   "status": "ok",
     *   "tenant": {
     *     "id": "00000000-0000-0000-0000-000000000000",
     *     "inserted_at": "2025-08-18T09:20:00Z",
     *     "name": "Building 4",
     *     "updated_at": "2025-08-19T10:15:00Z"
     *   }
     * }
     *
     * Error response:
     * {
     *   "message": "not found",
     *   "status": "error"
     * }
     * @phpstan-ignore-next-line
     */
    public static function get(string $id): array
    {
        $cfg    = Config::fromEnv();
        $client = new Client($cfg);

        $path = \SweetDate\Internal\RequestBuilder::path('/api/v1/tenants/:id', ['id' => $id]);
        $resp = $client->request('GET', $path);

        \SweetDate\Internal\ResponseValidator::assertStatus($resp, [200]);

        $raw  = (string) $resp->getBody();
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new \SweetDate\Exceptions\ApiException('invalid JSON from GET /tenants/:id', 500, $raw, $resp->getHeaders());
        }

        \SweetDate\Internal\ResponseValidator::requireKeys($json, ['status', 'tenant'], 'tenants.get');

        return $json;
    }


    /**
     * Update an existing tenant.
     * Command: TENANTS.UPDATE
     * endpoint /api/v1/tenants/:id
     *
     * @param string $id  Path parameter.
     * @param string $name
     *
     * Example response:
     *   {
     *     "status": "ok",
     *     "tenant": {
     *       "id": "00000000-0000-0000-0000-000000000000",
     *       "inserted_at": "2025-08-18T09:20:00Z",
     *       "name": "Terminal 3 3/4",
     *       "updated_at": "2025-08-19T10:15:00Z"
     *     }
     *   }
     *
     * Error response:
     *   {
     *     "details": {
     *       "name": [
     *         "can't be blank"
     *       ]
     *     },
     *     "message": "validation failed",
     *     "status": "error"
     *   }
     * @phpstan-ignore-next-line
     *    */
    public static function update(string $id, string $name): array
    {
        $cfg    = Config::fromEnv();
        $client = new Client($cfg);

        $path = \SweetDate\Internal\RequestBuilder::path('/api/v1/tenants/:id', ['id' => $id]);


        /** @var \Psr\Http\Message\ResponseInterface $resp */

        $resp = $client->request('PUT', $path, [
            'json' => ['name' => $name],
        ]);

        // Only 200 is OK; 404 and 422 will be thrown by ResponseValidator.
        \SweetDate\Internal\ResponseValidator::assertStatus($resp, [200]);

        $raw  = (string) $resp->getBody();
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new \SweetDate\Exceptions\ApiException('invalid JSON from PUT /tenants/:id', 500, $raw, $resp->getHeaders());
        }

        // Match the engine’s shape: { "status":"ok", "tenant": { ... } }
        \SweetDate\Internal\ResponseValidator::requireKeys($json, ['status', 'tenant'], 'tenants.update');

        return $json;
    }


    /**
    * Delete a tenant.
    * Command: TENANTS.DELETE
    * endpoint /api/v1/tenants/:id
    *
    *
    * Example response:
    *   {
    *     "status": "ok",
    *     "tenant": {
    *       "id": "00000000-0000-0000-0000-000000000000",
    *       "inserted_at": "2025-08-18T09:20:00Z",
    *       "name": "Terminal 3 3/4",
    *       "updated_at": "2025-08-19T10:15:00Z"
    *     }
    *   }
    *
    * Error response:
    *   {
    *     "message": "not found",
    *     "status": "error"
    *   }
    * @phpstan-ignore-next-line
    * */
    public static function delete(string $id): array
    {
        $cfg    = Config::fromEnv();
        $client = new Client($cfg);

        $path = \SweetDate\Internal\RequestBuilder::path('/api/v1/tenants/:id', ['id' => $id]);

        /** @var \Psr\Http\Message\ResponseInterface $resp */
        $resp = $client->request('DELETE', $path);

        // Only 200 is OK; 404 will be handled by ResponseValidator.
        \SweetDate\Internal\ResponseValidator::assertStatus($resp, [200]);

        $raw  = (string) $resp->getBody();
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new \SweetDate\Exceptions\ApiException('invalid JSON from DELETE /tenants/:id', 500, $raw, $resp->getHeaders());
        }

        // Match the engine’s shape: { "status":"ok", "tenant": { ... } }
        \SweetDate\Internal\ResponseValidator::requireKeys($json, ['status', 'tenant'], 'tenants.delete');

        return $json;
    }
}
