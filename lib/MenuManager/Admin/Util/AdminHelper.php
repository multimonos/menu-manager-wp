<?php

namespace MenuManager\Admin\Util;

class AdminHelper {
    public static function isPostListScreen( array $types ): bool {

        $screen = get_current_screen();

        return $screen
            && $screen->base === 'edit'
            && in_array( $screen->post_type, $types );
    }


}