<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class NodeMeta extends \Illuminate\Database\Eloquent\Model {

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
        error_log( self::TABLE );

        if ( ! db::load()::schema()->hasTable( self::TABLE ) ) {
            error_log( self::TABLE . ' not found' );
        } else {
            db::load()::schema()->dropIfExists( self::TABLE );
            error_log( self::TABLE . ' dropped' );
        }

        db::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->bigInteger( 'node_id' )->unsigned();
            $table->foreign( 'node_id' )->references( 'id' )->on( Node::TABLE )->onDelete( 'cascade' );
            $table->string( 'tags' )->nullable();
            $table->string( 'prices' )->nullable();
            $table->string( 'image_ids' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );
    }

    public function menuNode() {
        return $this->belongsTo( Node::class, 'node_id' );
    }
}