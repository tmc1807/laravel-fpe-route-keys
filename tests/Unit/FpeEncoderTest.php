<?php

use Tmc1807\LaravelFpeRouteKeys\Exceptions\InvalidRouteKeyException;
use Tmc1807\LaravelFpeRouteKeys\FpeEncoder;

it('round trips integer IDs into fixed length Base62 tokens', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    $token = $encoder->encode(15);

    expect($token)
        ->toHaveLength(11)
        ->and($encoder->decode($token))->toBe(15);
});

it('produces deterministic tokens for the same ID and context', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    expect($encoder->encode(15, 'users'))
        ->toBe($encoder->encode(15, 'users'))
        ->and($encoder->encode(15, 'users'))
        ->not->toBe($encoder->encode(15, 'orders'));
});

it('rejects malformed route keys', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    expect(fn (): int => $encoder->decode('not-a-route-key'))
        ->toThrow(InvalidRouteKeyException::class);
});

it('rejects negative and non-integer IDs', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    expect(fn (): string => $encoder->encode(-1))
        ->toThrow(InvalidArgumentException::class);

    expect(fn (): string => $encoder->encode('15.5'))
        ->toThrow(InvalidArgumentException::class);
});

it('supports the maximum signed PHP integer with the default token length', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    expect($encoder->decode($encoder->encode(PHP_INT_MAX)))->toBe(PHP_INT_MAX);
});

it('rejects IDs outside the configured FPE domain', function (): void {
    $encoder = new FpeEncoder('test-key', 11);

    expect(fn (): string => $encoder->encode('999999999999999999999'))
        ->toThrow(InvalidArgumentException::class);
});
