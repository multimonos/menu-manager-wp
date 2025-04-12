<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent\Casts;

use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\Castable;
use MenuManager\Vendor\Illuminate\Contracts\Database\Eloquent\CastsAttributes;
class AsArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                if (!isset($attributes[$key])) {
                    return;
                }
                $data = \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::decode($attributes[$key]);
                return \is_array($data) ? new \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\ArrayObject($data, \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\ArrayObject::ARRAY_AS_PROPS) : null;
            }
            public function set($model, $key, $value, $attributes)
            {
                return [$key => \MenuManager\Vendor\Illuminate\Database\Eloquent\Casts\Json::encode($value)];
            }
            public function serialize($model, string $key, $value, array $attributes)
            {
                return $value->getArrayCopy();
            }
        };
    }
}
