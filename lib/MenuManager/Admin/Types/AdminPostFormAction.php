<?php

namespace MenuManager\Admin\Types;
interface AdminPostFormAction extends AdminPostAction {
    public function form(): string;
}
