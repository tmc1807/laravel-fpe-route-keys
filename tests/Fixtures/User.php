<?php

namespace Tmc1807\LaravelFpeRouteKeys\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Tmc1807\LaravelFpeRouteKeys\Concerns\HasFpeRouteKey;

final class User extends Model
{
    use HasFpeRouteKey;

    public $timestamps = false;

    protected $guarded = [];
}
