<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KeyCaseConverter
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $isCamelRequested = strtolower($request->header('Key-Format') ?? '') === 'camel';

        if ($isCamelRequested) {
            $request->replace(
                $this->convertKeysToSnake($request->all())
            );
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if ($isCamelRequested && $response instanceof JsonResponse) {
            /** @var array<mixed> $data */
            $data = $response->getData(true);

            $response->setData(
                $this->convertKeysToCamel($data)
            );
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
