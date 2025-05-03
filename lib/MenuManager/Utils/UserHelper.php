<?php

namespace MenuManager\Utils;

class UserHelper {
    public static function currentUserEmail(): ?string {
        $user = wp_get_current_user();
        return $user ? $user->user_email : null;
    }

    public static function emailOrUnknown( ?string $email ): string {
        return empty( $email ) ? 'Unknown' : $email;
    }
}