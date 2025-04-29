<?php

namespace MenuManager\Model;

class Post {

    public $id;
    public \WP_Post $post;

    public function __construct( \WP_Post $post ) {
        $this->id = $post->ID;
        $post->id = $post->ID;
        $this->post = $post;
    }

    public static function table(): string {
        global $wpdb;
        return $wpdb->posts;
    }

    public static function type(): string {
        return 'post';
    }

    public static function isType( mixed $obj ): bool {
        return $obj instanceof \WP_Post && $obj->post_type === static::type();
    }

    public static function find( mixed $id_or_slug ): ?static {

        // by id
        if ( is_numeric( $id_or_slug ) ) {
            $post = get_post( (int)$id_or_slug );
            return ($post && $post->post_type === static::type())
                ? new static( $post )
                : null;
        }

        // by slug
        $post = get_page_by_path( $id_or_slug, OBJECT, static::type() );
        return $post instanceof \WP_Post
            ? new static( $post )
            : null;
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

    public static function create( array $data, array $meta = [] ): ?static {
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
//        foreach ( $meta as $field => $value ) {
//            update_post_meta( $id, $field, $value ); // @todo
//        }

        return static::find( $id );
    }

    public function update( array $data, array $meta = [] ): ?static {
        // Update.
        $post_data = array_merge( ['ID' => $this->post->ID], $data );

        $rs = wp_update_post( $post_data );

        if ( is_wp_error( $rs ) ) {
            return null;
        }

        return self::find( $rs );
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

    public static function deleteByPostId( $post_id, bool $force = true ): bool {
        return wp_delete_post( $post_id, $force ) instanceof \WP_Post;

    }

    public function delete( $force = true ): bool {
        return static::deleteByPostId( $this->post->ID, $force );
    }
}