<?php

namespace MenuManager\Admin\Types;

use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

interface AdminModelLinkAction extends AdminPostAction {
    public function link( Model $model ): string;
}