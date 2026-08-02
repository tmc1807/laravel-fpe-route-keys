<?php

namespace Tmc1807\LaravelFpeRouteKeys\Concerns;

use Tmc1807\LaravelFpeRouteKeys\Contracts\Encoder;
use Tmc1807\LaravelFpeRouteKeys\Exceptions\InvalidRouteKeyException;

trait HasFpeRouteKey
{
    public function getRouteKey()
    {
        if ($this->getKey() === null) {
            throw new \LogicException('An unsaved model cannot have an FPE route key.');
        }

        return app(Encoder::class)->encode($this->getKey(), $this->fpeRouteKeyContext());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->withTrashed()->first();
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (! $this->isFpeRouteKeyField($field)) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        try {
            $decoded = app(Encoder::class)->decode((string) $value, $this->fpeRouteKeyContext());
        } catch (InvalidRouteKeyException) {
            return $query->where($field ?: $this->getKeyName(), null);
        }

        return parent::resolveRouteBindingQuery($query, $decoded, $field ?: $this->getKeyName());
    }

    protected function fpeRouteKeyContext(): string
    {
        return static::class;
    }

    protected function isFpeRouteKeyField(?string $field): bool
    {
        $keyName = $this->getKeyName();

        return $field === null || $field === $keyName || $field === $this->qualifyColumn($keyName);
    }
}
