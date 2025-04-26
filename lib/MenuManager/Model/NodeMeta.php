<?php

namespace MenuManager\Model;

use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

class NodeMeta extends Model {

    const TABLE = 'mm_node_meta';
    protected $table = 'mm_node_meta';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'node_id',
        'tags',
        'prices',
        'image_ids',
    ];

    public static function createTable() {
        Logger::info( self::TABLE );

        if ( ! Database::load()::schema()->hasTable( self::TABLE ) ) {
            Logger::info( self::TABLE . ' table not found' );
        } else {
            Database::load()::schema()->dropIfExists( self::TABLE );
            Logger::info( self::TABLE . ' table dropped' );
        }

        Database::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->bigInteger( 'node_id' )->unsigned();
            $table->foreign( 'node_id' )->references( 'id' )->on( Node::TABLE )->onDelete( 'cascade' );
            $table->string( 'tags' )->nullable();
            $table->string( 'prices' )->nullable();
            $table->string( 'image_ids' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::TABLE . ' table created' );
    }

    public function node() {
        return $this->belongsTo( Node::class, 'node_id' );
    }

    public function hasTag( string $name ): bool {
        return empty( $this->tags ) ? false : in_array( $name, explode( ',', $this->tags ) );
    }
}