<?php

namespace MenuManager\Tasks\Backup;

use MenuManager\Model\Backup;
use MenuManager\Model\Node;
use MenuManager\Model\NodeMeta;
use MenuManager\Service\Database;
use MenuManager\Service\Filesystem;
use MenuManager\Service\Logger;
use MenuManager\Tasks\TaskResult;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Builder;


class CreateBackupTask {
    protected $models = [
        Node::class,
        NodeMeta::class,
    ];

    public function run(): TaskResult {
        // Dependencies
        Database::load();

        // Target
//        $dirpath = Backup::pathFor( '' );
//        $this->createBackupDirectoryIfNotExists( $dirpath );
        $filename = Filesystem::secureFilename( '.sql', 'backup-' );
        $target = Filesystem::pathFor( $filename );
        echo "\n$target";
        // Open file.
        if ( ! $f = Filesystem::open( $target, 'w' ) ) {
            return TaskResult::failure( 'Failed to open backup ' . $target );
        };

        // Open sql.
        $this->writeln( $f, "#" );
        $this->writeln( $f, "# Menu Manager MySQL data backup" );
        $this->writeln( $f, "#" );
        $this->writeln( $f, "# Generated: " . date( 'r' ) );
        $this->writeln( $f, "#" );
        $this->writeln( $f, "" );
        $this->writeln( $f, "" );
        $this->writeln( $f, "/*!40101 SET NAMES utf8 */;" );
        $this->writeln( $f, "" );
        $this->writeln( $f, "SET sql_mode='NO_AUTO_VALUE_ON_ZERO';" );
        $this->writeln( $f, "\n" );
        $this->writeln( $f, "SET FOREIGN_KEY_CHECKS = 0;" );
        $this->writeln( $f, "\n" );
        $this->writeln( $f, "START TRANSACTION;" );
        $this->writeln( $f, "\n" );

        // Collect + write sql.
        foreach ( $this->models as $model ) {

            $this->writeln( $f, "# --------------------------------------------------------" );
            $this->writeln( $f, "# Table `{$model::wptable()}`" );
            $this->writeln( $f, "#" );


            if ( $model::query()->count() > 0 ) {
                $this->writeln( $f, "" );
                $this->writeln( $f, "# Delete any existing data in `{$model::wptable()}`" );
                $this->writeln( $f, "" );
                $this->writeln( $f, "TRUNCATE TABLE `{$model::wptable()}`;" );
                $this->writeln( $f, "" );
                $this->writeln( $f, "# Data contents of `{$model::wptable()}`" );
                $this->streamEloquentInsert( $f, 500, $model::query() );
            } else {
                $this->writeln( $f, "# No data" );
            }

            $this->writeln( $f, "" );
            $this->writeln( $f, "#" );
            $this->writeln( $f, "# End table `{$model::wptable()}`" );
            $this->writeln( $f, "# --------------------------------------------------------" );
            $this->writeln( $f, "" );
            $this->writeln( $f, "" );
        }

        // Close sql.
        $this->writeln( $f, "" );
        $this->writeln( $f, "" );
        $this->writeln( $f, "COMMIT;" );
        $this->writeln( $f, "\n" );
        $this->writeln( $f, "SET FOREIGN_KEY_CHECKS = 1;" );
        $this->writeln( $f, "" );
        $this->writeln( $f, "# End of data" );
        $this->writeln( $f, "# --------------------------------------------------------" );

        // Close file.
        fclose( $f );

        if ( file_exists( $target ) ) {
            $backup = Backup::create( ['filename' => $filename] );
            return TaskResult::success( "Backup created {$target}.", $backup );
        }

        return TaskResult::failure( "Failed to create backup." );

    }

    protected function writeln( $f, $str ): void {
        fwrite( $f, $str . "\n" );
    }

    protected function createBackupDirectoryIfNotExists( $dirpath ): void {
        $fs = Filesystem::get();
        if ( ! $fs->exists( $dirpath ) ) {
            $fs->mkdir( $dirpath );
            Logger::taskInfo( 'backup', 'created ' . $dirpath );
        }
    }

    /**
     * @param resource $handle
     * @param int $chunkSize
     * @param Builder $query
     * @return void
     */
    protected function streamEloquentInsert( $handle, int $chunkSize, Builder $query ): void {
        $pdo = Database::load()->getConnection()->getPdo();

        $firstChunk = true;
        $firstRow = true;

        $query->chunk( $chunkSize, function ( $models ) use ( &$firstChunk, &$firstRow, $pdo, $handle ) {

            foreach ( $models as $model ) {
                $attrs = $model->attributesForInsert();
                $cols = array_keys( $attrs );

                // INSERT INTO ...
                if ( $firstChunk ) {
                    $table = $model::wptable();
                    $columns = join( ', ', array_map( fn( $x ) => "`{$x}`", $cols ) );
                    $this->writeln( $handle, sprintf( "INSERT INTO `%s` (%s) VALUES", $table, $columns ) );
                    $firstChunk = false;
                }

                // VALUES ...
                $quoted = array_map( fn( $x ) => $this->quote( $pdo, $x ), array_values( $attrs ) );
                $value_str = join( ', ', $quoted );
                $comma = $firstRow ? '' : ',';
                $this->writeln( $handle, sprintf( "%s (%s)", $comma, $value_str ) );

                // LOOP ctrls
                $firstRow = false;
            }

            $this->writeln( $handle, ';' );

        } );
    }

    protected function quote( $pdo, mixed $value ): string {
        return match (true) {
            is_null( $value ) => 'NULL',
            is_bool( $value ) => $value ? '1' : '0',
            is_numeric( $value ) => (string)$value,
            default => $pdo->quote( (string)$value ),
        };
    }
}