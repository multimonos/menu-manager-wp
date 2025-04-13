<?php

namespace MenuManager\Database\Model;

use MenuManager\Database\db;
use MenuManager\Logger;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;
use MenuManager\Vendor\Illuminate\Support\Collection;
use MenuManager\Vendor\Kalnoy\Nestedset\Collection as NestedSetCollection;
use MenuManager\Vendor\Kalnoy\Nestedset\NestedSet;
use MenuManager\Vendor\Kalnoy\Nestedset\NodeTrait;

enum NodeType: string {
    case Root = 'root';
    case Page = 'page';
    case Category0 = 'category-0';
    case Category1 = 'category-1';
    case Category2 = 'category-2';
    case Item = 'item';
    case Wine = 'wine';
    case OptionGroup = 'option-group';
    case Option = 'option';
    case AddonGroup = 'addon-group';
    case Addon = 'addon';
}

class Node extends Model {

    use NodeTrait;

    const TABLE = 'mm_node';
    protected $table = 'mm_node';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'uuid',
        'type',
        'title',
        'description',
    ];

    public static function createTable() {
        Logger::info( self::TABLE );

        if ( ! db::load()::schema()->hasTable( self::TABLE ) ) {
            Logger::info( self::TABLE . ' not found' );
        } else {
            db::load()::schema()->dropIfExists( self::TABLE );
            Logger::info( self::TABLE . ' dropped' );
        }

        db::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->bigInteger( 'menu_id' )->unsigned();
            $table->foreign( 'menu_id' )->references( 'ID' )->on( 'posts' )->onDelete( 'cascade' );
            $table->string( 'uuid', 64 )->nullable();
            NestedSet::columns( $table );
            $table->string( 'type', 32 );
            $table->string( 'title' )->nullable();
            $table->text( 'description' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::TABLE . ' created' );
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
        return Node::where( 'menu_id', $menu->ID )
            ->whereNotIn( 'type', [NodeType::Root->value, NodeType::Page->value] )
            ->count();
    }

    public static function findRootNode( \WP_Post $menu ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )
            ->where( 'type', NodeType::Root->value )
            ->first();
        return $node;
    }

    public static function findPageNames( \WP_Post $menu ): Collection {
        $root = self::findRootNode( $menu );
        return is_null( $root )
            ? []
            : $root->children->pluck( 'title' )->map( fn( $x ) => mb_strtolower( $x ) );
    }

    public static function findPageNode( \WP_Post $menu, string $page ): ?Node {
        $node = Node::where( 'menu_id', $menu->ID )
            ->where( 'type', NodeType::Page->value )
            ->where( 'title', $page )
            ->first();
        return $node;
    }

    public static function findRootTree( \WP_Post $menu ): ?NestedSetCollection {
        $root = self::findRootNode( $menu );
        if ( is_null( $root ) ) {
            return null;
        }
        $tree = Node::scoped( ['menu_id' => $menu->ID] )
            ->with( "meta" )
            ->withDepth()
            ->descendantsOf( $root->id )
            ->toTree();
        return $tree;
    }

    public static function findPageTree( \WP_Post $menu, string $page ): ?NestedSetCollection {
        $page = self::findPageNode( $menu, $page );
        if ( is_null( $page ) ) {
            return null;
        }
        $tree = Node::scoped( ['menu_id' => $menu->ID] )
            ->with( "meta" )
            ->withDepth()
            ->descendantsOf( $page->id )
            ->toTree();
        return $tree;
    }

}