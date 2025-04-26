<?php

namespace MenuManager\Admin;

use MenuManager\Admin\Menu\MenuService;

class AdminService {
    public static function init(): void {
        MenuService::init();
    }
}