<?php

namespace MenuManager\Database\Model;

use MenuManager\Database\db;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;
use MenuManager\Vendor\Illuminate\Database\Schema\Blueprint;

class Impex extends Model {

    const TABLE = 'mm_impex';
    protected $table = 'mm_impex';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'job_id',
        'action',
        'batch_id',
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
        'batch_id',
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
    const ON = 'yes';
    const OFF = 'no';

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
            $table->bigInteger( 'job_id' )->unsigned();
            $table->foreign( 'job_id' )->references( 'id' )->on( Job::TABLE )->onDelete( 'cascade' );
            $table->string( 'action', 32 );
            $table->string( 'menu', 32 );
            $table->string( 'page', 32 );
            $table->string( 'batch_id', 32 )->nullable();
            $table->string( 'type', 32 );
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

        error_log( self::TABLE . ' created' );
    }

    public function job() {
        return $this->belongsTo( Job::class, 'job_id' );
    }

    public static function isCategoryType( string $type ): bool {
        return str_contains( $type, 'category-' );
    }

    public static function isGroupType( string $type ): bool {
        $types = [
            'option-group',
            'addon-group',
        ];
        return in_array( $type, $types );
    }

    public static function isGroupItemType( string $type ): bool {
        return in_array( $type, [
            'option',
            'addon',
        ] );
    }

    public static function isItemType( string $type ): bool {
        return in_array( $type, [
            'item',
            'wine',
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