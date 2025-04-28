<?php

namespace MenuManager\Admin\Menu;

use MenuManager\Admin\Types\AdminPage;
use MenuManager\Model\Menu;


class MenuPageService implements AdminPage {

    public $postActions = [];

    public static function id(): string {
        return 'mm_menu_list';
    }

    public static function init(): void {

        $svc = new self;

        // post row actions
        $svc->postActions[] = new CloneMenuAction();
        $svc->postActions[] = new ExportCsvMenuAction();
        $svc->postActions[] = new ExportExcelMenuAction();
        $svc->postActions[] = new PreviewMenuAction();

        // ask post row actions to intialize themselves
        array_map(
            fn( $action ) => $action->register(),
            $svc->postActions
        );

        // Remove "duplicate post" function created by Post Duplicator plugin.  Priority must be high.
        add_filter( 'post_row_actions', [$svc, 'maybe_remove_duplicate_post_action'], 9999, 2 );
    }

    public function maybe_remove_duplicate_post_action( $actions, $post ) {
        // Remove "duplicate post" function created by Post Duplicator plugin.
        if ( Menu::isType( $post ) ) {
            unset( $actions['duplicate_post'] );
            unset( $actions['view'] );
            unset( $actions['edit'] );
        }
        return $actions;
    }
}