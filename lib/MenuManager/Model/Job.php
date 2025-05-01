<?php

namespace MenuManager\Model;


use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;

class Job extends Post {

    // @todo this should be custom table not a post type

    public static function type(): string {
        return 'mm_job';
    }

    public static function init() {
        add_action( 'init', function () {
            $labels = [
                'name'                  => _x( 'Jobs', 'Post type general name', 'menu-manager' ),
                'singular_name'         => _x( 'Job', 'Post type singular name', 'menu-manager' ),
                'job_name'              => _x( 'Jobs', 'Admin Job text', 'menu-manager' ),
                'name_admin_bar'        => _x( 'Job', 'Add New on Toolbar', 'menu-manager' ),
                'add_new'               => __( 'Add New', 'menu-manager' ),
                'add_new_item'          => __( 'Add New Job', 'menu-manager' ),
                'new_item'              => __( 'New Job', 'menu-manager' ),
                'edit_item'             => __( 'Edit Job', 'menu-manager' ),
                'view_item'             => __( 'View Job', 'menu-manager' ),
                'all_items'             => __( 'Jobs', 'menu-manager' ),
                'search_items'          => __( 'Search Jobs', 'menu-manager' ),
                'parent_item_colon'     => __( 'Parent Jobs:', 'menu-manager' ),
                'not_found'             => __( 'No jobs found.', 'menu-manager' ),
                'not_found_in_trash'    => __( 'No jobs found in Trash.', 'menu-manager' ),
                'featured_image'        => _x( 'Job Cover Image', 'Overrides the "Featured Image" phrase', 'menu-manager' ),
                'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'menu-manager' ),
                'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'menu-manager' ),
                'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'menu-manager' ),
                'archives'              => _x( 'Job archives', 'The post type archive label used in nav jobs', 'menu-manager' ),
                'insert_into_item'      => _x( 'Insert into job', 'Overrides the "Insert into post" phrase', 'menu-manager' ),
                'uploaded_to_this_item' => _x( 'Uploaded to this job', 'Overrides the "Uploaded to this post" phrase', 'menu-manager' ),
                'filter_items_list'     => _x( 'Filter jobs list', 'Screen reader text for the filter links', 'menu-manager' ),
                'items_list_navigation' => _x( 'Jobs list navigation', 'Screen reader text for the pagination', 'menu-manager' ),
                'items_list'            => _x( 'Jobs list', 'Screen reader text for the items list', 'menu-manager' ),
            ];

            $args = [
                'labels'             => $labels,
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => 'edit.php?post_type=' . Menu::type(), // show under menus nav
                'query_var'          => false,
                'rewrite'            => ['slug' => 'jobs'],
                'capability_type'    => 'post',
                'capabilities'       => [
                    'create_posts'       => 'do_not_allow',
                    'user_can_duplicate' => 'do_not_allow',
                ],
                'map_meta_cap'       => true,
                'has_archive'        => false,
                'hierarchical'       => false,
                'job_position'       => null,
                'supports'           => [
                    'title',
                ],
                'show_in_rest'       => false, // disable gutenberg
            ];

            register_post_type( self::type(), $args );
        } );
    }

    public static function create( array $data, array $meta = [] ): ?static {
        return parent::create( array_merge(
            ['post_name' => wp_generate_uuid4()],
            $data,
            $meta,
        ) );
    }

    public function impexes(): Collection {
        return Impex::where( 'job_id', $this->post->ID )->get();
    }
}