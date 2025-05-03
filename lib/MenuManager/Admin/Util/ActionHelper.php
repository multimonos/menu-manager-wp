<?php

namespace MenuManager\Admin\Util;

use MenuManager\Admin\Types\AdminAction;
use MenuManager\Model\Post;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class ActionHelper {
    public static function modelId( \WP_Post|Post|Model $model ): int|string {
        return $model instanceof \WP_Post ? $model->ID : $model->id;
    }

    public static function nonceSalt( ...$strs ): string {
        /* generate the string used to create a nonce */
        return join( '_', $strs );
    }

    public static function nonce( ...$strs ): string {
        return wp_create_nonce( self::nonceSalt( ...$strs ) );
    }

    public static function modelNonce( AdminAction $action, Model|Post|\WP_Post $model ): string {
        return wp_create_nonce( self::nonceSalt( $action->id(), self::modelId( $model ) ) );
    }

}