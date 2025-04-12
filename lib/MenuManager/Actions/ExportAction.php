<?php

namespace MenuManager\Actions;

use MenuManager\Database\db;
use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Node;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
use MenuManager\Vendor\League\Csv\Bom;
use MenuManager\Vendor\League\Csv\Writer;

class ExportAction {

    const FIELDS = [
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

    public function run( \WP_Post $menu, string $path ): ActionResult {

        db::load();
        db::load()::connection()->enableQueryLog();

        // PAGES
        $expected_count = Node::countForMenu( $menu );
        $pages = Node::findPageNames( $menu );

        // Collect rows ... write csv.
        echo "\n";

        // writer
        $writer = Writer::createFromPath( $path, 'w' );
//        $writer->setOutputBOM( Writer::BOM_UTF8 ); // Add BOM for UTF-8
        $writer->setOutputBOM( Bom::Utf8 );
//        $writer->setOutputBOM( Bom::Utf16Le );
//        $writer->setEnclosure( '"' );
        $writer->forceEnclosure();

        // headings
        $writer->insertOne( self::FIELDS );

        //  rows
        if ( ! empty( $pages ) ) {

            foreach ( $pages as $page ) {
                $tree = Node::findPageTree( $menu, $page );

                $rows = $this->visit( $tree, fn( Node $node ) => $this->create( $menu, $page, $node ) );

                foreach ( $rows as $row ) {
                    $writer->insertOne( self::array_fill_keys( self::FIELDS, $row ) );
                }
            }
        }

        $queries = db::load()::connection()->getQueryLog();

        echo "\n" . count( $queries ) . ' queries';
        echo "\nsource.count: " . $expected_count;
        echo "\nexport.count: " . count( $rows );
        echo "\n";


        return ActionResult::success( "Exported menu '" . $menu->post_name . "' to " . $path );
    }

    protected function visit( Collection $nodes, callable $callback ): array {

        $rows = [];

        foreach ( $nodes as $node ) {
            $nrow = $callback( $node );

            if ( ! is_null( $nrow ) ) {
                $rows[] = $nrow;
            }

            if ( $node->children->isNotEmpty() ) {
                $rows = array_merge(
                    $rows,
                    $this->visit( $node->children, $callback )
                );
            }
        }

//        echo "\n" . count( $rows );
        return $rows;
    }

    protected function create( \WP_Post $menu, string $page, Node $node ): ?array {

        // Only process these node types for export
        $simple_types = [
            'item',
            'option-group',
            'option',
            'addon',
            'addon-group',
            'wine',
        ];

        if ( in_array( $node->type, $simple_types ) ) {

            return [
                'action'         => $node->action,
                'menu'           => $menu->post_name,
                'page'           => $page,
                'batch_id'       => $node->batch_id,
                'item_id'        => '',//$node->id,
                'type'           => $node->type,
                'title'          => $node->title,
                'prices'         => (string)$node->meta->prices,
                'image_ids'      => (string)$node->meta->image_ids,
                'custom'         => '',
                'is_new'         => (string)($node->meta->hasTag( 'new' ) ? self::ON : self::OFF),
                'is_vegan'       => (string)($node->meta->hasTag( 'vegan' ) ? self::ON : self::OFF),
                'is_vegetarian'  => (string)($node->meta->hasTag( 'vegetarian' ) ? self::ON : self::OFF),
                'is_glutensmart' => (string)($node->meta->hasTag( 'gluten-smart' ) ? self::ON : self::OFF),
                'is_organic'     => (string)($node->meta->hasTag( 'organic' ) ? self::ON : self::OFF),
                'description'    => $node->description,
            ];

        } elseif ( Impex::isCategoryType( $node->type ) ) {

            return [
                'action'         => $node->action,
                'menu'           => $menu->post_name,
                'page'           => $page,
                'batch_id'       => $node->batch_id,
                'item_id'        => '',//$node->id,
                'type'           => $node->type,
                'title'          => $node->title,
                'prices'         => (string)$node->meta->prices,
                'image_ids'      => '',
                'custom'         => '',
                'is_new'         => '',
                'is_vegan'       => '',
                'is_vegetarian'  => '',
                'is_glutensmart' => '',
                'is_organic'     => '',
                'description'    => $node->description,
            ];
        }

        return null;
    }

    public static function array_fill_keys( array $keys, array $data, $fill_value = null ): array {
        $rs = [];
        foreach ( $keys as $k ) {
            $rs[$k] = $data[$k] ?? $fill_value;
        }
        return $rs;
    }
}