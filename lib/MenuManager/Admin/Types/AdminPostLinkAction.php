<?php

namespace MenuManager\Admin\Types;
interface AdminPostLinkAction extends AdminPostAction {
    public function link( \WP_Post $post ): string;
}