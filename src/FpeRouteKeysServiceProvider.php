<?php

namespace Tmc1807\LaravelFpeRouteKeys;

use Illuminate\Support\ServiceProvider;
use Tmc1807\LaravelFpeRouteKeys\Contracts\Encoder;

final class FpeRouteKeysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fpe-route-keys.php', 'fpe-route-keys');

        $this->app->singleton(FpeEncoder::class, function () {
            return new FpeEncoder(
                config('fpe-route-keys.key'),
                config('fpe-route-keys.length', 11),
                config('fpe-route-keys.tweak', 'laravel-fpe-route-keys'),
            );
        });

        $this->app->alias(FpeEncoder::class, Encoder::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/fpe-route-keys.php' => config_path('fpe-route-keys.php'),
        ], 'fpe-route-keys-config');
    }
}
