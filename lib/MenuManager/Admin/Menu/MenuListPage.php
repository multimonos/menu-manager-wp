<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\Types\AdminPage;
use MenuManager\Model\MenuPost;


class MenuListPage implements AdminPage {

    const POST_ROW_ACTIONS = [
        MenuPreviewPage::class,
        ExportCsvAction::class,
        ExportExcelAction::class,
        CloneAction::class,
    ];

    public static function id(): string {
        return 'mm_menu_list';
    }

    public static function init(): void {

        $svc = new self;

        // Remove "duplicate post" function created by Post Duplicator plugin.  Priority must be hig.
        add_filter( 'post_row_actions', [$svc, 'maybe_remove_duplicate_post_action'], 9999, 2 );
        add_filter( 'page_row_actions', [$svc, 'maybe_remove_duplicate_post_action'], 9999, 2 );

        // post row actions
        add_filter( 'post_row_actions', [$svc, 'add_post_row_actions'], 10, 2 );

        // post row action handlers
        array_map(
            fn( $action_class ) => add_action( 'admin_post_' . $action_class::id(), [$action_class, 'handle'] ),
            self::POST_ROW_ACTIONS
        );
    }

    public function add_post_row_actions( $actions, $post ) {
        if ( $post->post_type !== MenuPost::POST_TYPE ) {
            return $actions;
        }

        $new_actions = array_map(
            fn( $action_class ) => $actions[$action_class::id()] = $action_class::link( $post ),
            self::POST_ROW_ACTIONS
        );

        return array_merge( $actions, $new_actions );
    }


    public function maybe_remove_duplicate_post_action( $actions, $post ) {
        // Remove "duplicate post" function created by Post Duplicator plugin.
        if ( MenuPost::POST_TYPE === get_post_type( $post ) ) {
            unset( $actions['duplicate_post'] );
            unset( $actions['view'] );
            unset( $actions['edit'] );
        }
        return $actions;
    }
}