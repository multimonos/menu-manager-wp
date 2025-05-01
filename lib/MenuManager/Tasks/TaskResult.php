<?php

namespace MenuManager\Tasks;

class TaskResult {

    private bool $success = true;
    private string $message = "";
    private mixed $data = null;

    public function __construct( bool $success, string $message, mixed $data = null ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
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

    public static function success( string $message = "Ok", mixed $data = null ) {
        return new self( true, $message, $data );
    }

    public static function failure( string $message, mixed $data = null ) {
        return new self( false, $message, $data );
    }
}