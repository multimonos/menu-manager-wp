<?php

namespace MenuManager\Database\Model;

use MenuManager\Database\db;
use MenuManager\Logger;
use MenuManager\Utils\EnumTools;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

enum JobStatus: string {
    case Created = 'created';
    case Validated = 'validated';
    case Running = 'running';
    case Done = 'done';
}

enum JobType: string {
    case Import = 'import';
    case Export = 'export';
}

class Job extends Model {
    const TABLE = 'mm_jobs';
    protected $table = 'mm_jobs';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'type',
        'status',
        'source',
    ];

    public static function createTable() {
        Logger::info( 'created ' . self::TABLE );

        if ( ! db::load()::schema()->hasTable( self::TABLE ) ) {
            Logger::info( self::TABLE . ' table not found' );
        } else {
            db::load()::schema()->dropIfExists( self::TABLE );
            Logger::info( self::TABLE . ' table dropped' );
        }

        db::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->enum( 'type', EnumTools::values( JobType::class ) );
            $table->enum( 'status', EnumTools::values( JobStatus::class ) )->default( JobStatus::Created );
            $table->string( 'source' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
            $table->index( ['type', 'status'] );
        } );

        Logger::info( self::TABLE . ' table created' );
    }

    public function impexes() {
        return $this->hasMany( Impex::class, 'job_id' );
    }
}