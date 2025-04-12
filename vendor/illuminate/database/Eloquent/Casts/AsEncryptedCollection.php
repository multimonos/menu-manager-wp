<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent\Casts;

use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\Castable;
use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MenuManager\Vendor\Illuminate\Support\Collection;
use MenuManager\Vendor\Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
class AsEncryptedCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(protected array $arguments)
            {
            }
            public function get($model, $key, $value, $attributes)
            {
                $collectionClass = $this->arguments[0] ?? Collection::class;
                if (!\is_a($collectionClass, Collection::class, \true)) {
                    throw new InvalidArgumentException('The provided class must extend [' . Collection::class . '].');
                }
                if (isset($attributes[$key])) {
                    return new $collectionClass(\MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::decode(Crypt::decryptString($attributes[$key])));
                }
                return null;
            }
            public function set($model, $key, $value, $attributes)
            {
                if (!\is_null($value)) {
                    return [$key => Crypt::encryptString(\MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::encode($value))];
                }
                return null;
            }
        };
    }
}
