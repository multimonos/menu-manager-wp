<?php

namespace MenuManager\Admin\Types;

interface AdminPage {
    public static function id(): string;

    public static function init(): void;
}