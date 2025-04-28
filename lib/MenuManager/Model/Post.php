<?php

namespace MenuManager\Model;

use WP_Post;

class Post {

    public static function table(): string {
        global $wpdb;
        return $wpdb->posts;
    }

    public static function type() {
        return 'post';
    }

    public static function find( mixed $id_or_slug ): ?\WP_Post {

        // by id
        if ( is_numeric( $id_or_slug ) ) {
            $post = get_post( (int)$id_or_slug );
            return ($post && $post->post_type === static::type())
                ? $post
                : null;
        }

        // by slug
        $post = get_page_by_path( $id_or_slug, OBJECT, static::type() );
        return $post instanceof WP_Post ? $post : null;
    }

    public static function all( array $query = [] ): array {

        $defaults = [
            'post_type'   => static::type(),
            'post_status' => 'publish',
            'numberposts' => -1,
        ];

        $args = array_merge( $defaults, $query );

        $posts = get_posts( $args );

        return is_array( $posts ) ? $posts : [];
    }

    public static function evolve( \WP_Post $post ): \WP_Post {
        return $post;
    }

    public static function create( array $data, array $meta = [] ): ?\WP_Post {
        // Create post
        $post_defaults = [
            'post_type'   => static::type(),
            'post_status' => 'publish',
        ];

        $post_data = array_merge( $post_defaults, $data );

        $id = wp_insert_post( $post_data );

        // Early exit.
        if ( is_wp_error( $id ) ) {
            return null;
        }

        // Set meta.
        foreach ( $meta as $field => $value ) {
            update_post_meta( $id, $field, $value );
        }

        return static::find( $id );
    }

    public static function update( \WP_Post $post, array $data, array $meta = [] ): ?\WP_Post {
        // Update.
        $update_data = array_merge( ['ID' => $post->ID], $data );

        $rs = wp_update_post( $update_data );

        // Early exit
        if ( is_wp_error( $rs ) ) {
            return null;
        }

        // Set meta.
        foreach ( $meta as $field => $value ) {
            update_post_meta( $id, $field, $value );
        }

        // Refresh
        return static::find( $post->ID );
    }

    public static function dropTable(): bool {
        $query = new \WP_Query( [
            'post_type'      => static::type(),
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ] );

        if ( empty( $query->posts ) ) {
            return true;
        }

        // bulk delete
        $deleted = true;
        foreach ( $query->posts as $post_id ) {
            $deleted = $deleted && wp_delete_post( $post_id, true );
        }

        return $deleted;
    }

    public static function delete( $post_id, $force = true ): bool {
        $rs = wp_delete_post( $post_id, $force );
        return $rs instanceof \WP_Post;
    }
}