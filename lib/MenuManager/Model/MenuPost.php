<?php

namespace MenuManager\Model;


class MenuPost extends Post {

    public static function type() {
        return 'mm_menu';
    }

    public static function init() {
        add_action( 'init', function () {
            $labels = [
                'name'                  => _x( 'Menus', 'Post type general name', 'menu-manager' ),
                'singular_name'         => _x( 'Menu', 'Post type singular name', 'menu-manager' ),
                'menu_name'             => _x( 'Menus', 'Admin Menu text', 'menu-manager' ),
                'name_admin_bar'        => _x( 'Menu', 'Add New on Toolbar', 'menu-manager' ),
                'add_new'               => __( 'Add New', 'menu-manager' ),
                'add_new_item'          => __( 'Add New Menu', 'menu-manager' ),
                'new_item'              => __( 'New Menu', 'menu-manager' ),
                'edit_item'             => __( 'Edit Menu', 'menu-manager' ),
                'view_item'             => __( 'View Menu', 'menu-manager' ),
                'all_items'             => __( 'Menus', 'menu-manager' ),
                'search_items'          => __( 'Search Menus', 'menu-manager' ),
                'parent_item_colon'     => __( 'Parent Menus:', 'menu-manager' ),
                'not_found'             => __( 'No menus found.', 'menu-manager' ),
                'not_found_in_trash'    => __( 'No menus found in Trash.', 'menu-manager' ),
                'featured_image'        => _x( 'Menu Cover Image', 'Overrides the "Featured Image" phrase', 'menu-manager' ),
                'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'menu-manager' ),
                'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'menu-manager' ),
                'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'menu-manager' ),
                'archives'              => _x( 'Menu archives', 'The post type archive label used in nav menus', 'menu-manager' ),
                'insert_into_item'      => _x( 'Insert into menu', 'Overrides the "Insert into post" phrase', 'menu-manager' ),
                'uploaded_to_this_item' => _x( 'Uploaded to this menu', 'Overrides the "Uploaded to this post" phrase', 'menu-manager' ),
                'filter_items_list'     => _x( 'Filter menus list', 'Screen reader text for the filter links', 'menu-manager' ),
                'items_list_navigation' => _x( 'Menus list navigation', 'Screen reader text for the pagination', 'menu-manager' ),
                'items_list'            => _x( 'Menus list', 'Screen reader text for the items list', 'menu-manager' ),
            ];

            $args = [
                'labels'             => $labels,
                'public'             => false,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => ['slug' => 'menus'],
                'capability_type'    => 'post',
                'capabilities'       => [
                    'create_posts'       => 'do_not_allow',
                    'user_can_duplicate' => 'do_not_allow',
                ],
                'map_meta_cap'       => true,
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'menu_icon'          => 'dashicons-carrot',
                'supports'           => [
                    'title',
                    'editor',
                ],
                'show_in_rest'       => false, // disable gutenberg
            ];

            register_post_type( self::type(), $args );
        } );
    }

}