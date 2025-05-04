<?php

namespace MenuManager\Tasks;

class TaskResult {

    private bool $success = true;
    private string $message = "";
    private mixed $data = null;

    public function __construct() {
    }

    public function ok(): bool {
        return $this->success === true;
    }

    public function hasError(): bool {
        return $this->success === false;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getData(): mixed {
        return $this->data;
    }

    public function toJson( $with_data = false ): string {
        $obj = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ( $with_data ) {
            $obj['data'] = $this->data;
        }

        return json_encode( $obj );
    }

    public static function success( string $message = "Ok", mixed $data = null ) {
        $ts = new self;
        $ts->success = true;
        $ts->message = $message;
        $ts->data = $data;
        return $ts;
    }

    public static function failure( string $message, mixed $data = null ) {
        $ts = new self;
        $ts->success = false;
        $ts->message = $message;
        $ts->data = $data;
        return $ts;
    }
}