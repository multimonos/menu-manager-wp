<?php

namespace MenuManager\Admin\Types;

use MenuManager\Model\Post;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

interface AdminPostLinkAction extends AdminPostAction {
    /* html link generator for the row item */
    public function link( Model|Post|\WP_Post $model ): string;
}