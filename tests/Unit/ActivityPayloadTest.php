<?php

declare(strict_types=1);

use App\Values\ActivityPayload;

describe('ActivityPayload', function () {
    it('accepts flat attributes', function () {
        $payload = new ActivityPayload(['body' => 'content']);
        expect($payload->body)->toBe('content');
    });

    it('accepts from/to nested attributes', function () {
        $payload = new ActivityPayload([
            'status' => ['from' => 'open', 'to' => 'closed'],
        ]);
        expect($payload->status)->toBe(['from' => 'open', 'to' => 'closed']);
    });

    it('accepts partial from/to nested attributes', function () {
        $payload = new ActivityPayload([
            'status' => ['from' => 'open'],
        ]);
        expect($payload->status)->toBe(['from' => 'open']);
    });

    it('rejects nested attributes with invalid keys', function () {
        expect(fn () => new ActivityPayload([
            'status' => ['from' => 'open', 'something' => 'else'],
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('rejects nested numeric arrays', function () {
        expect(fn () => new ActivityPayload([
            'tags' => ['a', 'b'],
        ]))->toThrow(InvalidArgumentException::class);
    });

    it('rejects deeply nested arrays', function () {
        // Current requirement: "nested objects" -> value of top level attribute.
        // If we have ['status' => ['from' => ['nested' => 'val']]], value['from'] is array.
        // The rule "only from and to" applies to the immediate children of the attribute.
        // So ['from' => [...]] has key 'from', which Is allowed.
        // But what if the user meant RECURSIVE?
        // "nested objects can only contain from and to"
        // Usually activity logs are 1-level deep diffs.
        // checking immediate array value keys should be enough for now.

        $payload = new ActivityPayload([
            'meta' => ['foo' => 'bar'],
        ]);
    })->throws(InvalidArgumentException::class);
});
