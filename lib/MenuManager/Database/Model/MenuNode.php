<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet;
use Kalnoy\Nestedset\NodeTrait;
use MenuManager\Database\db;

class MenuNode extends \Illuminate\Database\Eloquent\Model {

    use NodeTrait;

    const TABLE = 'mm_menu_node';
    protected $table = 'mm_menu_node';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'level',
        'title',
        'description',
        'prices',
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

    public function menuItem() {
        // Always return an empty object if join does not exist
        // MenuNode -> MenuItem ( 1 to zero or one )
        return $this->hasOne( MenuItem::class, 'menu_node_id' )->withDefault();
    }

    public function saveWithParent( MenuNode $parent ): MenuNode {
        $this->parent_id = $parent->id;
//        $this->setParentId( $parent->id ); // not 100% reliable
        $this->save();
        $this->refresh();
        return $this;
    }
}