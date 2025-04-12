<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent\Casts;

use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\Castable;
use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MenuManager\Vendor\Illuminate\Support\Collection;
use InvalidArgumentException;
class AsCollection implements Castable
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
                if (!isset($attributes[$key])) {
                    return;
                }
                $data = \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::decode($attributes[$key]);
                $collectionClass = $this->arguments[0] ?? Collection::class;
                if (!\is_a($collectionClass, Collection::class, \true)) {
                    throw new InvalidArgumentException('The provided class must extend [' . Collection::class . '].');
                }
                return \is_array($data) ? new $collectionClass($data) : null;
            }
            public function set($model, $key, $value, $attributes)
            {
                return [$key => \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::encode($value)];
            }
        };
    }
}
