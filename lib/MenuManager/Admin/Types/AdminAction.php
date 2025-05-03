<?php

namespace MenuManager\Admin\Types;

interface AdminAction {

    /* unique id for the action */
    public function id(): string;

    /* name of the action */
    public function name(): string;

    /* call to register this thing with wordpress */
    public function register(): void;

    /* action handler */
    public function handle(): void;
}
