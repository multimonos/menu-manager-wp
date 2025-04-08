<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet;
use Kalnoy\Nestedset\NodeTrait;
use MenuManager\Database\db;

class MenuNode extends \Illuminate\Database\Eloquent\Model {
    const TABLE = 'mm_menu_node';
    protected $table = 'mm_menu_node';

    use NodeTrait;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'level',
        'title',
        'prices',
        'description',
    ];

    public function menuItems() {
        return $this->hasMany( MenuItem::class, 'menu_node_id' );
    }

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
            $table->bigInteger( 'menu_id' )->unsigned();
            $table->foreign( 'menu_id' )->references( 'ID' )->on( 'posts' )->onDelete( 'cascade' );
            NestedSet::columns( $table );
            $table->string( 'type', 32 );
            $table->tinyInteger( 'level' )->default( 0 );
            $table->string( 'title' )->nullable();
            $table->text( 'description' )->nullable();
            $table->string( 'prices', 64 )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );
    }


}