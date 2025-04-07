<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class MenuCategory extends \Illuminate\Database\Eloquent\Model {
    const TABLE = 'mm_menu_category';
    protected $table = 'mm_menu_category';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_page_id',
        'type',
        'title',
        'level',
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
            $table->bigInteger( 'menu_page_id' )->unsigned();
            $table->foreign( 'menu_page_id' )->references( 'id' )->on( MenuPage::TABLE )->onDelete( 'cascade' );
            $table->string( 'type', 32 );
            $table->string( 'title' );
            $table->tinyInteger( 'level' )->default( 1 );
            $table->text( 'description' )->nullable();
            $table->string( 'prices', 64 )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );
    }

    public function menuPage() {
        return $this->belongsTo( MenuPage::class, 'menu_page_id' );
    }

    public function menuItems() {
        return $this->hasMany( MenuItem::class, 'menu_category_id' );
    }

}