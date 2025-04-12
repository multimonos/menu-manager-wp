<?php

namespace MenuManager\Database\Model;

use MenuManager\Database\db;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

class Job extends Model {
    const TABLE = 'mm_jobs';
    protected $table = 'mm_jobs';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const STATUS_CREATED = 'created';
    const STATUS_VALIDATED = 'validated';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';

    protected $fillable = ['type', 'status', 'source'];

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
            $table->string( 'source' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
            $table->index( ['type', 'status'] );
        } );
        error_log( self::TABLE . ' created' );
    }

    public function impexes() {
        return $this->hasMany( Impex::class, 'job_id' );
    }
}