<?php

namespace MenuManager\Model;


use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;

class JobPost extends Post {

    public static function type(): string {
        return 'mm_job';
    }

    public static function init() {
        add_action( 'init', function () {
            $labels = [
                'name'                  => _x( 'Jobs', 'Post type general name', 'job-manager' ),
                'singular_name'         => _x( 'Job', 'Post type singular name', 'job-manager' ),
                'job_name'              => _x( 'Jobs', 'Admin Job text', 'job-manager' ),
                'name_admin_bar'        => _x( 'Job', 'Add New on Toolbar', 'job-manager' ),
                'add_new'               => __( 'Add New', 'job-manager' ),
                'add_new_item'          => __( 'Add New Job', 'job-manager' ),
                'new_item'              => __( 'New Job', 'job-manager' ),
                'edit_item'             => __( 'Edit Job', 'job-manager' ),
                'view_item'             => __( 'View Job', 'job-manager' ),
                'all_items'             => __( 'Jobs', 'job-manager' ),
                'search_items'          => __( 'Search Jobs', 'job-manager' ),
                'parent_item_colon'     => __( 'Parent Jobs:', 'job-manager' ),
                'not_found'             => __( 'No jobs found.', 'job-manager' ),
                'not_found_in_trash'    => __( 'No jobs found in Trash.', 'job-manager' ),
                'featured_image'        => _x( 'Job Cover Image', 'Overrides the "Featured Image" phrase', 'job-manager' ),
                'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'job-manager' ),
                'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'job-manager' ),
                'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'job-manager' ),
                'archives'              => _x( 'Job archives', 'The post type archive label used in nav jobs', 'job-manager' ),
                'insert_into_item'      => _x( 'Insert into job', 'Overrides the "Insert into post" phrase', 'job-manager' ),
                'uploaded_to_this_item' => _x( 'Uploaded to this job', 'Overrides the "Uploaded to this post" phrase', 'job-manager' ),
                'filter_items_list'     => _x( 'Filter jobs list', 'Screen reader text for the filter links', 'job-manager' ),
                'items_list_navigation' => _x( 'Jobs list navigation', 'Screen reader text for the pagination', 'job-manager' ),
                'items_list'            => _x( 'Jobs list', 'Screen reader text for the items list', 'job-manager' ),
            ];

            $args = [
                'labels'             => $labels,
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => 'edit.php?post_type=' . MenuPost::type(), // show under menus nav
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