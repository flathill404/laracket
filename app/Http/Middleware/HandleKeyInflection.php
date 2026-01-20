<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HandleKeyInflection
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $isCamelRequested = strtolower($request->header('Key-Inflection') ?? '') === 'camel';

        if ($isCamelRequested) {
            $request->replace(
                $this->convertKeysToSnake($request->all())
            );
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if ($isCamelRequested && $response instanceof JsonResponse) {
            /** @var mixed $data */
            $data = $response->getData(true);

            // Some responses, such as those from Laravel Fortify, may return objects (e.g., Eloquent models) instead of arrays.
            // In such cases, getData(true) returns the object as-is, so we need to convert it to an array.
            if (! is_array($data)) {
                $data = json_decode((string) json_encode($data), true);
            }

            if (is_array($data)) {
                $response->setData(
                    $this->convertKeysToCamel($data)
                );
            }
        }

        return $response;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    private function convertKeysToSnake(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = Str::snake((string) $key);

            if (is_array($value)) {
                $value = $this->convertKeysToSnake($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    private function convertKeysToCamel(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = Str::camel((string) $key);

            if (is_array($value)) {
                $value = $this->convertKeysToCamel($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }
}
