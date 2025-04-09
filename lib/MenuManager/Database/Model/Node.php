<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use Kalnoy\Nestedset\NestedSet;
use Kalnoy\Nestedset\NodeTrait;
use MenuManager\Database\db;

class Node extends \Illuminate\Database\Eloquent\Model {

    use NodeTrait;

    const TABLE = 'mm_node';
    protected $table = 'mm_node';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'title',
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
            $table->bigInteger( 'menu_id' )->unsigned();
            $table->foreign( 'menu_id' )->references( 'ID' )->on( 'posts' )->onDelete( 'cascade' );
            NestedSet::columns( $table );
            $table->string( 'type', 32 );
            $table->string( 'title' )->nullable();
            $table->text( 'description' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );
    }

    public function meta() {
        // Always return an empty object if join does not exist
        // Node -> NodeMeta ( 1 to zero or one )
        return $this->hasOne( NodeMeta::class, 'node_id' )->withDefault();
    }

    public function saveWithParent( Node $parent ): Node {
//        $this->setParentId( $parent->id ); // not 100% reliable
        $this->parent_id = $parent->id;
        $this->save();
        $this->refresh();
        return $this;
    }

    public static function findRootNode( \WP_Post $menu ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )->where( 'type', 'root' )->first();
        return $node;
    }

    public static function findPageNode( \WP_Post $menu, string $page ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )->where( 'type', 'page' )->where( 'title', $page )->first();
        return $node;
    }

    public static function findRootTree( \WP_Post $menu ): ?\Kalnoy\NestedSet\Collection {
        $root = self::findRootNode( $menu );
        if ( is_null( $root ) ) {
            return null;
        }
        $tree = Node::with( "meta" )->withDepth()->descendantsOf( $root->id )->toTree();
        return $tree;
    }


    public static function findPageTree( \WP_Post $menu, string $page ): ?\Kalnoy\NestedSet\Collection {
        $page = self::findPageNode( $menu, $page );
        if ( is_null( $page ) ) {
            return null;
        }
        $tree = Node::with( "meta" )->withDepth()->descendantsOf( $page->id )->toTree();
        return $tree;
    }
}