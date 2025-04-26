<?php

namespace MenuManager\Model;

use WP_Post;

class Post {

    const POST_TYPE = 'post';

    public static function type() {
        return static::POST_TYPE;
    }

    public static function find( mixed $id_or_slug ): ?WP_Post {

        // by id
        if ( is_numeric( $id_or_slug ) ) {
            $post = get_post( (int)$id_or_slug );
            return ($post && $post->post_type === static::POST_TYPE)
                ? $post
                : null;
        }

        // by slug
        $post = get_page_by_path( $id_or_slug, OBJECT, static::POST_TYPE );
        return $post instanceof WP_Post ? $post : null;
    }

    public static function all( array $query = [] ): array {

        $defaults = [
            'post_type'   => static::POST_TYPE,
            'numberposts' => -1,
        ];

        $args = array_merge( $defaults, $query );

        $posts = get_posts( $args );

        return is_array( $posts ) ? $posts : [];
    }

    public static function create( array $data ): mixed {
        $ndata = array_merge( $data, [
            'post_type'   => static::POST_TYPE,
            'post_status' => 'publish',
        ] );

        $id = wp_insert_post( $ndata );

        if ( $id ) {
            return self::find( $id );
        }

        return $id;
    }

    public static function delete( $post_id, $force = true ): bool {
        $rs = wp_delete_post( $post_id, $force );
        return $rs instanceof \WP_Post;
    }
}