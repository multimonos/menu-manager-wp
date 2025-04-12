<?php

namespace MenuManager\Database\Factory;

use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\Node;

class ExportNodeFactory {

    public static function createRow( \WP_Post $menu, string $page, Node $node ): ?array {

        $simple_types = [
            'item',
            'option-group',
            'option',
            'addon',
            'addon-group',
            'wine',
        ];

        if ( in_array( $node->type, $simple_types ) ) {
            return self::createMenuitemRow( $menu, $page, $node );

        } elseif ( Impex::isCategoryType( $node->type ) ) {
            return self::createCategoryRow( $menu, $page, $node );
        }

        return null;
    }

    public static function createCategoryRow( \WP_Post $menu, string $page, Node $node ): array {

        return [
            'action'         => $node->action,
            'menu'           => $menu->post_name,
            'page'           => $page,
            'batch_id'       => $node->batch_id,
            'item_id'        => $node->id,
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

    public static function createMenuitemRow( \WP_Post $menu, string $page, Node $node ): array {
        return [
            'action'         => $node->action,
            'menu'           => $menu->post_name,
            'page'           => $page,
            'batch_id'       => $node->batch_id,
            'item_id'        => $node->id,
            'type'           => $node->type,
            'title'          => $node->title,
            'prices'         => (string)$node->meta->prices,
            'image_ids'      => (string)$node->meta->image_ids,
            'custom'         => '',
            'is_new'         => (string)($node->meta->hasTag( 'new' ) ? Impex::ON : Impex::OFF),
            'is_vegan'       => (string)($node->meta->hasTag( 'vegan' ) ? Impex::ON : Impex::OFF),
            'is_vegetarian'  => (string)($node->meta->hasTag( 'vegetarian' ) ? Impex::ON : Impex::OFF),
            'is_glutensmart' => (string)($node->meta->hasTag( 'gluten-smart' ) ? Impex::ON : Impex::OFF),
            'is_organic'     => (string)($node->meta->hasTag( 'organic' ) ? Impex::ON : Impex::OFF),
            'description'    => $node->description,
        ];

    }
}