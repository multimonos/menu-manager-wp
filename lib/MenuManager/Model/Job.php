<?php


namespace MenuManager\Model;

use MenuManager\Model\Traits\ModelExtras;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Service\Logger;
use MenuManager\Utils\EnumTools;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

enum JobStatus: string {
    case Created = 'created';
    case Validated = 'validated';
    case Running = 'running';
    case Done = 'done';
}

/*enum JobType: string {
    case Import = 'import';
    case Export = 'export';
}*/

class Job extends Model {

    use ModelExtras;

    protected $table = 'mm_job';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'status',
        'title',
        'filename',
        'lastrun_at',
        'lastrun_by',
        'created_by',
    ];

    public static function createTable() {
        Logger::info( 'created ' . self::table() );

        if ( ! Database::load()::schema()->hasTable( self::table() ) ) {
            Logger::info( self::table() . ' table not found' );
        } else {
            Database::load()::schema()->dropIfExists( self::table() );
            Logger::info( self::table() . ' table dropped' );
        }

        Database::load()::schema()->create( self::table(), function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
//            $table->enum( 'type', EnumTools::values( JobType::class ) );
            $table->enum( 'status', EnumTools::values( JobStatus::class ) )->default( JobStatus::Created );
            $table->string( 'title' )->nullable();
            $table->string( 'filename' )->nullable();
            $table->string( 'lastrun_by' )->nullable();
            $table->dateTime( 'lastrun_at' )->nullable();
            $table->string( 'created_by' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
//            $table->index( ['type', 'status'] );
        } );

        Logger::info( self::table() . ' table created' );
    }

    public function impexes() {
        return $this->hasMany( Impex::class, 'job_id' );
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