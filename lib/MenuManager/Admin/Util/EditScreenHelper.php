<?php

namespace MenuManager\Admin\Util;


use MenuManager\Admin\Types\AdminPostAction;

class EditScreenHelper {
    public static function isEditScreen( string $post_type ): bool {

        $screen = get_current_screen();

        return $screen
            && $screen->base === 'edit'
            && $screen->post_type === $post_type;
    }

    public static function postCreatedAt( int|string $post_id ): string {
        return get_the_date( 'F d, Y \a\t H:i a', $post_id );
    }

    public static function style( string $post_type, string $style ): void {
        add_action( 'admin_head', function () use ( $post_type, $style ) {
            if ( ! self::isEditScreen( $post_type ) ) {
                return;
            }
            echo '<style>' . $style . '</style>';
        } );
    }

    public static function disablePostRowActions( string $post_type ): void {
        add_filter( 'post_row_actions', function ( $actions, $post ) use ( $post_type ) {
            if ( $post instanceof \WP_Post && $post->post_type === $post_type ) {
                return [];
            }
            return $actions;
        }, 9997, 2 );
    }

    public static function removePostRowActions( string $post_type, array $action_ids ): void {
        add_filter( 'post_row_actions', function ( $actions, $post ) use ( $post_type, $action_ids ) {
            if ( $post instanceof \WP_Post && $post->post_type === $post_type ) {
                foreach ( $action_ids as $key ) {
                    unset( $actions[$key] );
                }
            }
            return $actions;
        }, 9998, 2 );
    }

    public static function columnTitles( string $post_type, callable $callback ) {
        add_filter( "manage_edit-{$post_type}_columns", $callback );
    }

    public static function columnData( string $post_type, callable $callback ) {
        add_filter( "manage_{$post_type}_posts_custom_column", $callback, 10, 2 );
    }

    public static function sortableColumns( string $post_type, callable $callback ) {
        add_filter( "manage_edit-{$post_type}_sortable_columns", $callback );
    }

    public static function registerAdminPostActions( mixed $actions ): void {
        foreach ( $actions as $action ) {
            if ( ! in_array( AdminPostAction::class, class_implements( $action ) ) ) {
                continue;
            }
            $action->register();
        }
    }
}