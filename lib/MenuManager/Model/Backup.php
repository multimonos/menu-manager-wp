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

    public static function pathFor( string $filename ): string {
        return trailingslashit( wp_upload_dir()['basedir'] . '/mm-backup/' ) . $filename;
    }

    public function filepath(): string {
        return self::pathFor( $this->filename );
    }

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
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::table() . ' table created' );
    }

    public function delete() {
        $filepath = self::pathFor( $this->filename );

        $fs = Filesystem::get();

        $rs = parent::delete();

        if ( $rs && $fs->exists( $this->filepath() ) ) {
            $fs->delete( $this->filepath() );
        }

        return $rs;
    }
}