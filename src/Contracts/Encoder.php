<?php

namespace Tmc1807\LaravelFpeRouteKeys\Contracts;

interface Encoder
{
    public function encode(int|string $id, ?string $context = null): string;

    public function decode(string $token, ?string $context = null): int;
}
