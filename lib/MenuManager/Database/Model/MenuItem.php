<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class MenuItem extends \Illuminate\Database\Eloquent\Model {
    const TABLE = 'mm_menu_item';
    protected $table = 'mm_menu_item';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_id',
        'type',
        'title',
        'image_ids',
        'prices',
        'description',
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
            $table->bigInteger( 'menu_category_id' )->unsigned();
            $table->foreign( 'menu_category_id' )->references( 'id' )->on( MenuCategory::TABLE )->onDelete( 'cascade' );
            $table->string( 'type', 32 );
            $table->string( 'title' );
            $table->text( 'description' )->nullable();
            $table->string( 'prices' )->nullable();
            $table->string( 'image_ids' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );
    }

    public function menuCategory() {
        return $this->belongsTo( MenuCategory::class, 'menu_category_id' );
    }
}