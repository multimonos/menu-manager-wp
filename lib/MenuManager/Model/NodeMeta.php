<?php

namespace MenuManager\Model;

use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

class NodeMeta extends Model {

    protected $table = 'mm_node_meta';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'node_id',
        'tags',
        'prices',
        'image_ids',
    ];

    public static function table(): string {
        return (new static)->getTable();
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
            $table->bigInteger( 'node_id' )->unsigned();
            $table->foreign( 'node_id' )->references( 'id' )->on( Node::table() )->onDelete( 'cascade' );
            $table->string( 'tags' )->nullable();
            $table->string( 'prices' )->nullable();
            $table->string( 'image_ids' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::table() . ' table created' );
    }

    public function node() {
        return $this->belongsTo( Node::class, 'node_id' );
    }

    public function hasTag( string $name ): bool {
        return empty( $this->tags ) ? false : in_array( $name, explode( ',', $this->tags ) );
    }
}