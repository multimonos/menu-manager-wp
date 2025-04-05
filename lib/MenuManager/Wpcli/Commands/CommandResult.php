<?php

namespace MenuManager\Wpcli\Commands;

class CommandResult {

    private $success = true;
    private $message = "";

    public function __construct( bool $success, string $message, mixed $data = null ) {
        $this->success = $success;
        $this->message = $message;
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

    public static function success( string $message = "", mixed $data = null ) {
        return new self( true, $message, $data );
    }

    public static function failure( string $message, mixed $data = null ) {
        return new self( false, $message, $data );
    }
}