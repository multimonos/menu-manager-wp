<?php

namespace MenuManager\Model;

use MenuManager\Model\Traits\ModelExtras;
use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Types\NodeType;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;
use MenuManager\Vendor\Illuminate\Support\Collection;
use MenuManager\Vendor\Kalnoy\Nestedset\Collection as NestedSetCollection;
use MenuManager\Vendor\Kalnoy\Nestedset\NestedSet;
use MenuManager\Vendor\Kalnoy\Nestedset\NodeTrait;


class Node extends Model {

    use NodeTrait;
    use ModelExtras;

    protected $table = 'mm_node';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'type' => NodeType::class,
    ];

    protected $with = ['meta'];

    protected $fillable = [
        'menu_id',
        'parent_id',
        'uuid',
        'type',
        'title',
        'description',
        'sort_order',
    ];

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
            $table->bigInteger( 'menu_id' )->unsigned();
            $table->foreign( 'menu_id' )->references( 'ID' )->on( 'posts' )->onDelete( 'cascade' );
            $table->string( 'uuid', 64 )->nullable();
            NestedSet::columns( $table );
            $table->string( 'type', 32 );
            $table->string( 'title' )->nullable();
            $table->text( 'description' )->nullable();
            $table->integer( 'sort_order' )->unsigned()->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::table() . ' table created' );
    }

    protected function getScopeAttributes() {
        return ['menu_id'];
    }

    public function isPage(): bool {
        return $this->type === NodeType::Page;
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

    public static function countForMenu( Menu $menu ): int {
        return Node::where( 'menu_id', $menu->post->ID )
            ->whereNotIn( 'type', [NodeType::Root->value, NodeType::Page->value] )
            ->count();
    }

    public static function findRootNode( Menu $menu ): ?Node {
        $node = Node::where( 'menu_id', $menu->post->ID )
            ->where( 'type', NodeType::Root->value )
            ->first();
        return $node;
    }

    public static function findPageNames( Menu $menu ): Collection {
        $root = self::findRootNode( $menu );
        return is_null( $root )
            ? new Collection()
            : $root->children->pluck( 'title' )->map( fn( $x ) => mb_strtolower( $x ) );
    }

    public static function findPageNode( Menu $menu, string $page ): ?Node {
        $node = Node::where( 'menu_id', $menu->post->ID )
            ->where( 'type', NodeType::Page->value )
            ->where( 'title', $page )
            ->first();
        return $node;
    }

    public static function getSortedMenu( Menu $menu, Node $parent ): ?NestedSetCollection {

        $tree = Node::scoped( ['menu_id' => $menu->post->ID] )
            // ->defaultOrder() // must not be present
            ->with( "meta" )
            ->withDepth()
            ->orderBy( 'depth' )
            ->orderBy( 'sort_order' ) // NULL first
//            ->orderByRaw( 'COALESCE(sort_order, 9999)' ) // NULL last
            ->descendantsOf( $parent->id ) // position of this call matters.
            ->toTree();

        return $tree;
    }

}