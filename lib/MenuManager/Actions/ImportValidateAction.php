<?php

namespace MenuManager\Actions;

class ImportValidateAction {
    public function run( $job_id ): ActionResult {
        return ActionResult::success();
    }
}