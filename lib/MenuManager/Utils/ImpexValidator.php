<?php

namespace MenuManager\Utils;

class ImpexValidator {
    protected $errors = [];

    public function isValid() {
        return count( $this->errors ) === 0;
    }

    public function getErrors( $unique_only = false ): array {
        return $unique_only ? array_unique( $this->errors ) : $this->errors;
    }

    public function addError( string $error ): void {
        $this->errors[] = $error;
    }

    // assertions

    public function assertNotEmptyString( string $error, mixed $val ): void {
        if ( (string)$val !== '' ) {
            return;
        };
        $this->addError( $error );
    }

    public function assertEmptyString( string $error, mixed $val ): void {
        if ( (string)$val === '' ) {
            return;
        };
        $this->addError( $error );
    }

    public function assertEmptyOrId( string $error, mixed $val ): void {
        if ( (string)$val === '' || intval( $val ) > 0 ) {
            return;
        };
        $this->addError( $error );
    }

    public function assertId( string $error, mixed $val ): void {
        if ( intval( $val ) > 0 ) {
            return;
        }
        $this->addError( $error );
    }

    public function assertEmptyOrPositveInteger( string $error, mixed $val ): void {
        if ( (string)$val === '' || intval( $val ) > 0 ) {
            return;
        };
        $this->addError( $error );
    }

    public function assertEmptyOrPipeDelimited( string $error, mixed $val ): void {
        if ( (string)$val === '' || preg_match( '/^[^|]+(\|[^|]+)*$/', $val ) ) {
            return;
        };
        $this->addError( $error );
    }

    public function assertEnum( string $error, mixed $val, string $enum_class ): void {
        if ( $enum_class::tryFrom( $val ) ) {
            return;
        }
        $this->addError( $error );
    }

    public function assertEmptyOrEnum( string $error, mixed $val, string $enum_class ): void {
        if ( (string)$val === '' || $enum_class::tryFrom( $val ) ) {
            return;
        }
        $this->addError( $error );
    }

    public function assertImageIdsValid( string $error, mixed $val, array $lookup ): void {
        // Can be empty.
        if ( (string)$val === '' ) {
            return;
        }

        // Must be key in lookup.
        $ids = Splitter::split( $val, '|' );
        $invalid = array_filter( $ids, fn( $id ) => ! isset( $lookup[$id] ) );

        if ( empty( $invalid ) ) {
            return;
        }
        $this->addError( sprintf( $error, join( ', ', $invalid ) ) );
    }
}