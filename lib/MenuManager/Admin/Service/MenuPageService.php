<?php

namespace MenuManager\Admin\Service;

use MenuManager\Admin\Actions\CloneMenuAction;
use MenuManager\Admin\Actions\ExportCsvMenuAction;
use MenuManager\Admin\Actions\ExportExcelMenuAction;
use MenuManager\Admin\Actions\PreviewMenuAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;


class MenuPageService implements AdminPage {

    public static function id(): string {
        return 'mm_menus';
    }

    public static function init(): void {

        $svc = new self;

        // ask post row actions to intialize themselves
        array_map(
            fn( $action ) => $action->register(),
            [
                new CloneMenuAction(),
                new ExportCsvMenuAction(),
                new ExportExcelMenuAction(),
                new PreviewMenuAction(),
            ]
        );

        EditScreenHelper::removePostRowActions( Menu::type(), [
            'duplicate_post',
            'view',
            'edit',
        ] );
    }
}