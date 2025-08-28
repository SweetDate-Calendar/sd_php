<?php

declare(strict_types=1);

namespace SweetDate;

final class Tenants
{
  /** @param array<string,mixed> $params */
  public static function getList(array $params = []): array
  {
    $client = Client::fromEnv();
    $q = [];
    if (isset($params['limit']))  $q['limit']  = (int)$params['limit'];
    if (isset($params['offset'])) $q['offset'] = (int)$params['offset'];
    if (isset($params['q']))      $q['q']      = (string)$params['q'];

    return $client->request('GET', '/api/v1/tenants', $q, null);
  }
}
