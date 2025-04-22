<?php

namespace MenuManager\Database\Factory;

use MenuManager\Database\Model\Impex;
use MenuManager\Database\Model\ImpexBoolean;
use MenuManager\Database\Model\Node;
use MenuManager\Database\Model\NodeType;

class ExportNodeFactory {

    public static function toBoolean( bool $val ): ImpexBoolean {
        return $val === true ? ImpexBoolean::True : ImpexBoolean::False;
    }

    public static function createRow( \WP_Post $menu, string $page, Node $node ): ?array {

        $simple_types = [
            NodeType::Item,
            NodeType::OptionGroup,
            NodeType::Option,
            NodeType::AddonGroup,
            NodeType::Addon,
            NodeType::Wine,
        ];


        if ( in_array( $node->type, $simple_types ) ) {
            return self::createMenuitemRow( $menu, $page, $node );

        } elseif ( Impex::isCategoryType( $node->type->value ) ) {
            return self::createCategoryRow( $menu, $page, $node );
        }

        return null;
    }

    public static function createCategoryRow( \WP_Post $menu, string $page, Node $node ): array {

        return [
            'action'         => '',
            'menu'           => $menu->post_name,
            'page'           => $page,
            'uuid'           => $node->uuid,
            'parent_id'      => $node->parent_id,
            'sort_order'     => $node->sort_order,
            'item_id'        => $node->id,
            'type'           => $node->type->value, // valid or throw
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
            'action'         => '',
            'menu'           => $menu->post_name,
            'page'           => $page,
            'uuid'           => $node->uuid,
            'parent_id'      => $node->parent_id,
            'sort_order'     => $node->sort_order,
            'item_id'        => $node->id,
            'type'           => $node->type->value, // valid or throw
            'title'          => $node->title,
            'prices'         => (string)$node->meta->prices,
            'image_ids'      => (string)$node->meta->image_ids,
            'custom'         => '',
            'is_new'         => self::toBoolean( $node->meta->hasTag( 'new' ) )->value,
            'is_vegan'       => self::toBoolean( $node->meta->hasTag( 'vegan' ) )->value,
            'is_vegetarian'  => self::toBoolean( $node->meta->hasTag( 'vegetarian' ) )->value,
            'is_glutensmart' => self::toBoolean( $node->meta->hasTag( 'gluten-smart' ) )->value,
            'is_organic'     => self::toBoolean( $node->meta->hasTag( 'organic' ) )->value,
            'description'    => $node->description,
        ];

    }
}