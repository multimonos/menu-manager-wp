<?php

namespace MenuManager\Model;

use MenuManager\Service\Database;
use MenuManager\Service\Logger;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

enum ImpexAction: string {
    case Update = 'update';
    case Insert = 'insert';
    case Delete = 'delete';
    case Price = 'price';
}


enum ImpexBoolean: string {
    case True = 'yes';
    case False = 'no';
}


class Impex extends Model {

    const TABLE = 'mm_impex';
    protected $table = 'mm_impex';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'job_id',
        'action',
        'uuid',
        'parent_id',
        'sort_order',
        'description',
        'image_ids',
        'is_glutensmart',
        'is_new',
        'is_organic',
        'is_vegan',
        'is_vegetarian',
        'item_id',
        'menu',
        'page',
        'prices',
        'title',
        'type',
    ];

    // CSV Related
    const CSV_FIELDS = [
        'action',
        'menu',
        'page',
        'uuid',
        'parent_id',
        'sort_order',
        'type',
        'item_id',
        'title',
        'prices',
        'image_ids',
        'is_new',
        'is_glutensmart',
        'is_organic',
        'is_vegan',
        'is_vegetarian',
        'custom',
        'description',
    ];

    public static function createTable() {
        Logger::info( self::TABLE );

        if ( ! Database::load()::schema()->hasTable( self::TABLE ) ) {
            Logger::info( self::TABLE . ' table not found' );
        } else {
            Database::load()::schema()->dropIfExists( self::TABLE );
            Logger::info( self::TABLE . ' table dropped' );
        }

        Database::load()::schema()->create( self::TABLE, function ( Blueprint $table ) {
            $table->bigIncrements( 'id' );
            $table->bigInteger( 'job_id' )->unsigned();
            $table->foreign( 'job_id' )->references( 'id' )->on( Job::TABLE )->onDelete( 'cascade' );
            $table->string( 'action', 32 ); // @todo should this be enum? ... no let user make mistake,catch in validation
            $table->string( 'menu', 32 );
            $table->string( 'page', 32 );
            $table->string( 'uuid', 64 )->nullable();
            $table->bigInteger( 'parent_id' )->unsigned()->nullable();
            $table->integer( 'sort_order' )->unsigned()->nullable();
            $table->string( 'type', 32 ); // @todo should this be enum? ... no let user make mistake,catch in validation
            $table->bigInteger( 'item_id' )->nullable();
            $table->string( 'title' )->nullable();
            $table->string( 'prices', 64 )->nullable();
            $table->string( 'image_ids', 64 )->nullable();
            $table->boolean( 'is_glutensmart' )->default( false );
            $table->boolean( 'is_new' )->default( false );
            $table->boolean( 'is_organic' )->default( false );
            $table->boolean( 'is_vegan' )->default( false );
            $table->boolean( 'is_vegetarian' )->default( false );
            $table->text( 'description' )->nullable();
            $table->dateTime( 'created_at' )->useCurrent();
            $table->dateTime( 'updated_at' )->useCurrent();
        } );

        Logger::info( self::TABLE . ' table created' );
    }

    public function job() {
        return $this->belongsTo( Job::class, 'job_id' );
    }

    public static function isType( string $type, array $allowed ): bool {
        return in_array( NodeType::tryFrom( $type ), $allowed );
    }

    public static function isCategoryType( string $type ): bool {
        return self::isType( $type, [
            NodeType::Category0,
            NodeType::Category1,
            NodeType::Category2,
        ] );
    }

    public static function isGroupType( string $type ): bool {
        return self::isType( $type, [
            NodeType::OptionGroup,
            NodeType::AddonGroup,
        ] );
    }

    public static function isGroupItemType( string $type ): bool {
        return self::isType( $type, [
            NodeType::Option,
            NodeType::Addon,
        ] );
    }

    public static function isItemType( string $type ): bool {
        return self::isType( $type, [
            NodeType::Item,
            NodeType::Wine,
        ] );
    }

    public static function levelFromType( string $type ): int {
        return (int)preg_replace( '/\D*/', '', $type );
    }

    public static function collectTags( Impex $row ): string {
        $fieldmap = [
            'is_glutensmart' => 'gluten-smart',
            'is_new'         => 'new',
            'is_organic'     => 'organic',
            'is_vegan'       => 'vegan',
            'is_vegetarian'  => 'vegetarian',
        ];

        $tags = array_filter( array_map(
            fn( $field ) => self::toBoolean( $row->$field ) ? $fieldmap[$field] : null,
            array_keys( $fieldmap )
        ) );

        return join( ',', $tags );
    }

    public static function toBoolean( mixed $v ) {
        if ( is_bool( $v ) ) {
            return $v;
        }
        if ( is_numeric( $v ) ) {
            return (float)$v != 0.0;
        }

        $v = is_string( $v ) ? strtolower( trim( $v ) ) : $v;

        return match ($v) {
            1, '1', 'true', 'yes', 'on', 'y' => true,
            default => false,
        };
    }
}