# laravel-fpe-route-keys

[![PHP](https://img.shields.io/badge/PHP-8.2+-8892BF?logo=php&logoColor=fff)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-red?logo=laravel&logoColor=fff)](https://laravel.com)
[![Tests](https://github.com/tmc1807/laravel-fpe-route-keys/actions/workflows/tests.yml/badge.svg)](https://github.com/tmc1807/laravel-fpe-route-keys/actions)
[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Format-preserving encrypted route keys untuk model Eloquent Laravel.
Package ini mengubah integer primary key menjadi token Base62 dengan panjang tetap,
tanpa menyimpan token tambahan di database.

```text
/users/15       -> /users/k8QmP2xY7Aa
```

Token bersifat deterministik: model yang sama selalu menghasilkan route key yang
sama selama encryption key dan konfigurasi tidak berubah.

## Fitur

- Menggunakan FPE berbasis AES melalui FFX radix.
- Token Base62 yang pendek dan URL-safe.
- Tidak membutuhkan kolom database tambahan.
- Terintegrasi dengan implicit route model binding Laravel.
- Memisahkan token antar-model menggunakan model class sebagai context.
- Mendukung Laravel 11, 12, dan 13.

## Persyaratan

- PHP 8.2 atau lebih baru.
- Laravel 11, 12, atau 13.
- Extension PHP `gmp` dan `openssl`.
- Primary key model berupa integer non-negatif.

## Instalasi

```bash
composer require tmc1807/laravel-fpe-route-keys
```

Laravel akan mendaftarkan service provider secara otomatis. Jika package discovery
dinonaktifkan, daftarkan provider secara manual:

```php
Tmc1807\LaravelFpeRouteKeys\FpeRouteKeysServiceProvider::class,
```

Package menggunakan `APP_KEY` sebagai key default. Konfigurasi dapat dipublish:

```bash
php artisan vendor:publish --tag=fpe-route-keys-config
```

## Penggunaan

Tambahkan trait ke model yang ingin menggunakan FPE route key:

```php
use Illuminate\Database\Eloquent\Model;
use Tmc1807\LaravelFpeRouteKeys\Concerns\HasFpeRouteKey;

class User extends Model
{
    use HasFpeRouteKey;
}
```

Route dan controller tetap menggunakan implicit binding Laravel:

```php
Route::get('/users/{user}', function (User $user) {
    return $user->name;
})->name('users.show');
```

Saat model diberikan ke helper `route()`, package otomatis menggunakan token:

```php
route('users.show', $user);
// /users/k8QmP2xY7Aa
```

Laravel kemudian mendekripsi token dan mencari model berdasarkan primary key
aslinya. URL dengan token invalid akan menghasilkan response `404`.

## Konfigurasi

Konfigurasi berada di `config/fpe-route-keys.php`:

```php
return [
    'key' => env('FPE_ROUTE_KEYS_KEY'),
    'length' => 11,
    'tweak' => 'laravel-fpe-route-keys',
];
```

`key` akan menggunakan `APP_KEY` jika kosong. Jangan mengganti `APP_KEY` atau
`FPE_ROUTE_KEYS_KEY` tanpa strategi migrasi, karena token lama tidak dapat
dikembalikan dengan key baru.

`length` default 11. Panjang yang lebih besar dapat dipilih untuk domain ID yang
lebih luas. Token menggunakan alfabet Base62 berikut:

```text
0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz
```

## API Encoder

Encoder dapat digunakan langsung melalui container Laravel:

```php
use Tmc1807\LaravelFpeRouteKeys\Contracts\Encoder;

$encoder = app(Encoder::class);
$token = $encoder->encode(15, 'users');
$id = $encoder->decode($token, 'users');
```

Context bersifat opsional dan sebaiknya digunakan jika token dibuat di luar model.
Trait model otomatis menggunakan fully-qualified model class sebagai context.

## Catatan keamanan

FPE menyembunyikan nilai ID, tetapi bukan pengganti authorization. Terapkan policy,
permission check, dan rate limiting seperti biasa. Siapa pun yang memiliki URL
valid tetap dapat menggunakannya jika endpoint tidak dilindungi.

Package ini menggunakan FPE deterministik, bukan token acak tersimpan. Karena itu
tidak ada query pemetaan tambahan, tetapi nilai yang sama selalu menghasilkan token
yang sama. Jangan membuat algoritma FPE sendiri atau mengganti dependency kriptografi
dengan XOR, hash biasa, atau operasi matematika sederhana.

## Pengujian

```bash
composer install
composer test
composer pint
```

## Lisensi

MIT
