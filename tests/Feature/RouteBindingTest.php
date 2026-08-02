<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Tmc1807\LaravelFpeRouteKeys\Tests\Fixtures\User;

it('uses an FPE route key when generating URLs and resolving implicit binding', function (): void {
    Route::get('/users/{user}', fn (User $user): string => (string) $user->getKey())
        ->middleware(SubstituteBindings::class)
        ->name('users.show');

    $user = User::create(['name' => 'Budi']);
    $url = route('users.show', $user);

    expect($user->getRouteKey())
        ->not->toBe((string) $user->getKey())
        ->toHaveLength(11);

    $this->get($url)
        ->assertOk()
        ->assertSeeText('1');
});

it('returns a not found response for an invalid FPE route key', function (): void {
    Route::get('/users/{user}', fn (User $user): string => (string) $user->getKey())
        ->middleware(SubstituteBindings::class);

    $this->get('/users/not-a-valid-key')
        ->assertNotFound();
});
