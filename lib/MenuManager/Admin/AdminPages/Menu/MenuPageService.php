<?php

namespace MenuManager\Admin\AdminPages\Menu;

use MenuManager\Admin\AdminPages\Menu\Actions\CloneMenuAction;
use MenuManager\Admin\AdminPages\Menu\Actions\ExportCsvMenuAction;
use MenuManager\Admin\AdminPages\Menu\Actions\ExportExcelMenuAction;
use MenuManager\Admin\AdminPages\Menu\Actions\PreviewMenuAction;
use MenuManager\Admin\Types\AdminPage;
use MenuManager\Admin\Util\EditScreenHelper;
use MenuManager\Model\Menu;


class MenuPageService implements AdminPage {

    public static function id(): string {
        return 'mm_menus';
    }

    public static function init(): void {

        EditScreenHelper::registerAdminPostActions( [
            new CloneMenuAction(),
            new ExportCsvMenuAction(),
            new ExportExcelMenuAction(),
            new PreviewMenuAction(),
        ] );

        EditScreenHelper::removePostRowActions( Menu::type(), [
            'duplicate_post',
            'view',
            'edit',
        ] );
    }
}