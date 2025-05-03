<?php

namespace MenuManager\Admin\Types;
interface AdminFormAction extends AdminAction {
    public function form(): string;
}
