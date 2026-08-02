<?php

namespace Tmc1807\LaravelFpeRouteKeys;

use InvalidArgumentException;
use Janv\FFXRadix\FFXRadix;
use Throwable;
use Tmc1807\LaravelFpeRouteKeys\Contracts\Encoder;
use Tmc1807\LaravelFpeRouteKeys\Exceptions\FpeRouteKeysConfigurationException;
use Tmc1807\LaravelFpeRouteKeys\Exceptions\InvalidRouteKeyException;

final class FpeEncoder implements Encoder
{
    public const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    private FFXRadix $cipher;

    private string $key;

    private int $length;

    private string $tweak;

    private mixed $maximum;

    public function __construct(?string $key = null, int $length = 11, string $tweak = 'laravel-fpe-route-keys')
    {
        if ($length < 2 || $length > 64) {
            throw new FpeRouteKeysConfigurationException('The FPE route key length must be between 2 and 64 characters.');
        }

        $key ??= function_exists('config') ? config('app.key') : null;

        if (! is_string($key) || $key === '') {
            throw new FpeRouteKeysConfigurationException('An application key is required to encode route keys.');
        }

        $this->cipher = new FFXRadix('AES-256');
        $this->key = $this->normaliseKey($key);
        $this->length = $length;
        $this->tweak = $tweak;
        $this->maximum = gmp_sub(gmp_pow(gmp_init('62', 10), $length), 1);
    }

    public function encode(int|string $id, ?string $context = null): string
    {
        $id = $this->normaliseId($id);
        $number = gmp_init($id, 10);

        if (gmp_cmp($number, $this->maximum) > 0) {
            throw new InvalidArgumentException("The ID {$id} cannot be represented by a {$this->length}-character route key.");
        }

        $plainText = str_pad(gmp_strval($number, 62), $this->length, '0', STR_PAD_LEFT);

        return $this->cipher->encrypt($plainText, 62, $this->key, $this->tweak($context));
    }

    public function decode(string $token, ?string $context = null): int
    {
        if (strlen($token) !== $this->length || strspn($token, self::ALPHABET) !== $this->length) {
            throw new InvalidRouteKeyException('The route key has an invalid format.');
        }

        try {
            $plainText = $this->cipher->decrypt($token, 62, $this->key, $this->tweak($context));
            $number = gmp_init($plainText, 62);
        } catch (Throwable $exception) {
            throw new InvalidRouteKeyException('The route key could not be decoded.', 0, $exception);
        }

        if (gmp_cmp($number, gmp_init((string) PHP_INT_MAX, 10)) > 0) {
            throw new InvalidRouteKeyException('The route key resolves to an unsupported ID.');
        }

        return (int) gmp_strval($number, 10);
    }

    private function normaliseId(int|string $id): string
    {
        if (is_int($id)) {
            if ($id < 0) {
                throw new InvalidArgumentException('FPE route keys only support non-negative integer IDs.');
            }

            return (string) $id;
        }

        if (! preg_match('/\A\d+\z/D', $id)) {
            throw new InvalidArgumentException('FPE route keys only support non-negative integer IDs.');
        }

        return ltrim($id, '0') ?: '0';
    }

    private function normaliseKey(string $key): string
    {
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded === false || $decoded === '') {
                throw new FpeRouteKeysConfigurationException('The base64 application key is invalid.');
            }

            $key = $decoded;
        }

        return hash('sha256', $key, true);
    }

    private function tweak(?string $context): string
    {
        return hash_hmac('sha256', $context ?? '', $this->tweak, true);
    }
}
