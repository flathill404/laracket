<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleKeyInflection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

describe('HandleKeyInflection', function () {
    it('converts request keys to snake_case when header is present', function () {
        $middleware = new HandleKeyInflection;

        $request = Request::create('/', 'POST', ['camelKey' => 'value', 'nestedCamel' => ['innerKey' => 'innerValue']]);
        $request->headers->set('Key-Inflection', 'camel');

        $middleware->handle($request, function ($req) {
            expect($req->all())->toBe([
                'camel_key' => 'value',
                'nested_camel' => ['inner_key' => 'innerValue'],
            ]);

            return new JsonResponse([]);
        });
    });

    it('converts response keys to camelCase when header is present', function () {
        $middleware = new HandleKeyInflection;

        $request = Request::create('/', 'GET');
        $request->headers->set('Key-Inflection', 'camel');

        $response = $middleware->handle($request, function () {
            return new JsonResponse(['snake_key' => 'value', 'nested_snake' => ['inner_key' => 'innerValue']]);
        });

        expect($response->getData(true))->toBe([
            'snakeKey' => 'value',
            'nestedSnake' => ['innerKey' => 'innerValue'],
        ]);
    });

    it('does not convert keys without header', function () {
        $middleware = new HandleKeyInflection;

        $request = Request::create('/', 'POST', ['camelKey' => 'value']);

        $response = $middleware->handle($request, function ($req) {
            expect($req->all())->toBe(['camelKey' => 'value']);

            return new JsonResponse(['snake_key' => 'value']);
        });

        expect($response->getData(true))->toBe(['snake_key' => 'value']);
    });

    it('handles arrays recursively', function () {
        $middleware = new HandleKeyInflection;

        $request = Request::create('/', 'POST', [
            'levelOne' => [
                'levelTwo' => [
                    'levelThree' => 'value',
                ],
            ],
        ]);
        $request->headers->set('Key-Inflection', 'camel');

        $middleware->handle($request, function ($req) {
            expect($req->all())->toBe([
                'level_one' => [
                    'level_two' => [
                        'level_three' => 'value',
                    ],
                ],
            ]);

            return new JsonResponse([]);
        });
    });
});
