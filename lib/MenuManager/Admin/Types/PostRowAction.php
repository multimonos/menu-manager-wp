<?php

namespace MenuManager\Admin\Types;

interface PostRowAction {

    public static function id(): string;

    public static function link( \WP_Post $post ): string;

    public static function handle(): void;

}