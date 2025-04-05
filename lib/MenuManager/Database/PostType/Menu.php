<?php

namespace MenuManager\Database\PostType;


class Menu extends Post {

    const POST_TYPE = 'menus';

    public static function init() {
        add_action( 'init', function () {

            $o = new self;

            register_post_type( self::POST_TYPE, [
                'labels'          => [
                    'name'          => 'Menus',
                    'singular_name' => 'Menu',
                ],
                'public'          => false, // not public
                'show_ui'         => true,  // show in admin
                'show_in_menu'    => true,  // include in admin menu
                'show_in_rest'    => false, // disable gutenberg
                'supports'        => ['title', 'editor'],
                'capability_type' => 'post',
                'menu_icon'       => 'dashicons-carrot',

            ] );
        } );
    }
}