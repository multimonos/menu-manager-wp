<?php

namespace MenuManager\Model;

use MenuManager\Model\Traits\ModelExtras;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Service\Logger;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

class Backup extends Model {

    use ModelExtras;

    protected $table = 'mm_backup';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'filename',
    ];

    public static function createTable() {
        Logger::info( self::table() );

        if ( ! Database::load()::schema()->hasTable( self::table() ) ) {
            Logger::info( self::table() . ' table not found' );
        } else {
            Database::load()::schema()->dropIfExists( self::table() );
            Logger::info( self::table() . ' table dropped' );
        }

        Database::load()::schema()->create( self::table(), function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->string( 'filename' )->nullable();
            $table->dateTime( 'lastrun_at' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::table() . ' table created' );
    }

    public function delete() {
        $fs = Filesystem::get();

        $path = Filesystem::pathFor( $this->filename );

        $rs = parent::delete();

        if ( $rs && $fs->exists( $path ) ) {
            $fs->delete( $path );
        }

        return $rs;
    }
}