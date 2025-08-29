<?php

declare(strict_types=1);

namespace SweetDate\Internal;

/**
 * Small helper for path templating + query encoding.
 *
 * Supports both :id and {id} placeholder styles.
 */
final class RequestBuilder
{
    /**
     * Build a path from a template with placeholders.
     *
     * Examples:
     *  path('/api/v1/tenants/:id', ['id' => 'abc']) => '/api/v1/tenants/abc'
     *  path('/api/v1/users/{user_id}', ['user_id' => 'u-1']) => '/api/v1/users/u-1'
     *
     * @param array<string, int|float|bool|string|\Stringable> $params
     */
    public static function path(string $template, array $params = []): string
    {
        $out = $template;

        foreach ($params as $key => $value) {
            $val = rawurlencode((string) $value);

            // :key style
            $out = str_replace(':' . $key, $val, $out);
            // {key} style
            $out = str_replace('{' . $key . '}', $val, $out);
        }

        // Ensure leading slash (strict canonicalization)
        if ($out === '' || $out[0] !== '/') {
            $out = '/' . ltrim($out, '/');
        }

        return $out;
    }

    /**
     * @param array<string, int|float|string|bool|array<int|string, scalar>|null> $query
     */
    public static function withQuery(string $path, array $query = []): string
    {
        if ($query === []) {
            return $path;
        }

        $qs = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $path . (str_contains($path, '?') ? '&' : '?') . $qs;
    }
}
