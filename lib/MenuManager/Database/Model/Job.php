<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class Job extends \Illuminate\Database\Eloquent\Model {


    // Eloquent
    const TABLE = 'mm_jobs';

    const STATUS_CREATED = 'created';
    const STATUS_VALIDATED = 'validated';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'mm_jobs';
    protected $fillable = ['type', 'status'];

    public static function createTable() {
        error_log( self::TABLE );

        if ( ! db::load()::schema()->hasTable( self::TABLE ) ) {
            error_log( self::TABLE . ' not found' );
        } else {
            db::load()::schema()->dropIfExists( self::TABLE );
            error_log( self::TABLE . ' dropped' );
        }

        db::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->enum( 'type', ['import', 'export'] );
            $table->enum( 'status', ['created', 'validated', 'running', 'done'] )->default( 'created' );
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
            $table->index( ['type', 'status'] );
        } );
        error_log( self::TABLE . ' created' );
    }
}