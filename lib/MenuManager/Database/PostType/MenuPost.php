<?php

namespace MenuManager\Database\PostType;


use Illuminate\Database\Eloquent\Builder;
use MenuManager\Database\db;
use MenuManager\Database\Model\MenuPage;

class MenuPost extends Post {

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

    public static function menuPages( \WP_Post $menu ): Builder {
        db::load();
        $pages = MenuPage::where( 'menu_post_id', $menu->ID );
        return $pages;
    }
}