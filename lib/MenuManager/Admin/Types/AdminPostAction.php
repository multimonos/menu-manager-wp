<?php

namespace MenuManager\Admin\Types;

interface AdminPostAction {

    /* unique id for the action */
    public function id(): string;

    /* call to register this thing with wordpress */
    public function register(): void;

    /* action handler */
    public function handle(): void;
}
