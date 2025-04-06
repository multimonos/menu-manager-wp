<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class Menu extends \Illuminate\Database\Eloquent\Model {
    const TABLE = 'mm_menus';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'mm_menus';
    protected $fillable = [
        'menu_post_id',
        'page',
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
            $table->bigInteger( 'menu_post_id' )->unsigned();
            $table->foreign( 'menu_post_id' )->references( 'ID' )->on( 'posts' )->onDelete( 'restrict' );
            $table->string( 'page', 32 );
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        error_log( self::TABLE . ' created' );
    }
}