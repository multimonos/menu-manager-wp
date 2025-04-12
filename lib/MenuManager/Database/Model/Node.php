<?php

namespace MenuManager\Database\Model;

use MenuManager\Database\db;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;
use MenuManager\Vendor\Illuminate\Support\Collection;
use MenuManager\Vendor\Kalnoy\Nestedset\Collection as NestedSetCollection;
use MenuManager\Vendor\Kalnoy\Nestedset\NestedSet;
use MenuManager\Vendor\Kalnoy\Nestedset\NodeTrait;

class Node extends Model {

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

    protected function getScopeAttributes() {
        return ['menu_id'];
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

    public static function countForMenu( \WP_Post $menu ): int {
        return Node::where( 'menu_id', $menu->ID )->whereNotIn( 'type', ['root', 'page'] )->count();
    }

    public static function findRootNode( \WP_Post $menu ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )->where( 'type', 'root' )->first();
        return $node;
    }

    public static function findPageNames( \WP_Post $menu ): Collection {
        $root = self::findRootNode( $menu );
        return is_null( $root )
            ? []
            : $root->children->pluck( 'title' )->map( fn( $x ) => mb_strtolower( $x ) );
    }

    public static function findPageNode( \WP_Post $menu, string $page ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )->where( 'type', 'page' )->where( 'title', $page )->first();
        return $node;
    }

    public static function findRootTree( \WP_Post $menu ): ?NestedSetCollection {
        $root = self::findRootNode( $menu );
        if ( is_null( $root ) ) {
            return null;
        }
        $tree = Node::scoped( ['menu_id' => $menu->ID] )->with( "meta" )->withDepth()->descendantsOf( $root->id )->toTree();
        return $tree;
    }

    public static function findPageTree( \WP_Post $menu, string $page ): ?NestedSetCollection {
        $page = self::findPageNode( $menu, $page );
        if ( is_null( $page ) ) {
            return null;
        }
        $tree = Node::scoped( ['menu_id' => $menu->ID] )->with( "meta" )->withDepth()->descendantsOf( $page->id )->toTree();
        return $tree;
    }

}