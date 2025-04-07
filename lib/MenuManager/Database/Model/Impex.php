<?php

namespace MenuManager\Database\Model;

use Illuminate\Database\Schema\Blueprint;
use MenuManager\Database\db;

class Impex extends \Illuminate\Database\Eloquent\Model {

    const TABLE = 'mm_impex';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'mm_impex';
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
            $table->string( 'tags' )->nullable();
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

    public function isGroup(): bool {
        $types = [
            'option-group',
            'addon-group',
        ];
        return in_array( $this->type, $types );
    }

    public function isCategory(): bool {
        return str_contains( (string)$this->type, 'category' );
    }

    public function isMenuItem(): bool {
        return in_array( $this->type, [
            'item',
            'option',
            'addon',
            'wine',
        ] );
    }

    public static function extractCategoryLevel( Impex $item ): int {
        return (int)preg_replace( '/\D*/', '', $item->type );
    }

    public static function menuCategoryOf( Impex $item ): MenuCategory {
        return new MenuCategory( [
            'title'       => $item->title,
            'type'        => $item->type,
            'level'       => self::extractCategoryLevel( $item ),
            'prices'      => $item->prices,
            'description' => $item->description,
        ] );
    }


    public static function menuItemOf( Impex $item ): MenuItem {
        return new MenuItem( [
            'title'       => $item->title,
            'type'        => $item->type,
            'prices'      => $item->prices,
            'description' => $item->description,
            'image_ids'   => $item->image_ids,
        ] );
    }
}