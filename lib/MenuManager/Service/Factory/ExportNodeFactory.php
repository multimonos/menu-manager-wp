<?php

namespace MenuManager\Service\Factory;

use MenuManager\Model\Impex;
use MenuManager\Model\ImpexBoolean;
use MenuManager\Model\Menu;
use MenuManager\Model\Node;
use MenuManager\Model\NodeType;
use MenuManager\Tasks\Menu\ExportTaskPeer;

// @todo Perhaps this class should create models of Impex and then the caller can transform to array?
class ExportNodeFactory {

    public static function toBoolean( bool $val ): ImpexBoolean {
        return $val === true ? ImpexBoolean::True : ImpexBoolean::False;
    }

    public static function createRow( Menu $menu, Node $page, Node $node ): ?array {

        $row = match (true) {

            // Pages
            $node->type === NodeType::Page => self::pageRow( $menu, $node ),

            // Categories
            Impex::isCategoryType( $node->type->value ) => self::categoryRow( $menu, $page, $node ),

            // Simple menuitem types...typically depth 2 or higher.
            in_array( $node->type, [
                NodeType::Item,
                NodeType::OptionGroup,
                NodeType::Option,
                NodeType::AddonGroup,
                NodeType::Addon,
                NodeType::Wine,
            ] ) => self::menuitemRow( $menu, $page, $node ),

            // Default
            default => null,
        };

        // Evolve each row array to be dense with the required Impex model fields.
        $dense_row = ExportTaskPeer::fillArray( Impex::CSV_FIELDS, $row );

        return $dense_row;
    }

    public static function pageRow( Menu $menu, Node $node ): array {
        /* create sparse data array */
        return [
            'menu'       => $menu->post->post_name,
            'page'       => $node->title,
            'uuid'       => $node->uuid,
            'parent_id'  => $node->parent_id,
            'sort_order' => $node->sort_order,
            'item_id'    => $node->id,
            'type'       => $node->type->value, // valid or throw
            'title'      => $node->title,
        ];
    }


    public static function categoryRow( Menu $menu, Node $page, Node $node ): array {
        /* create sparse data array */
        return [
            'menu'        => $menu->post->post_name,
            'page'        => $page->title,
            'uuid'        => $node->uuid,
            'parent_id'   => $node->parent_id,
            'sort_order'  => $node->sort_order,
            'item_id'     => $node->id,
            'type'        => $node->type->value, // valid or throw
            'title'       => $node->title,
            'prices'      => (string)$node->meta->prices,
            'description' => $node->description,
        ];
    }

    public static function menuitemRow( Menu $menu, Node $page, Node $node ): array {
        /* create sparse data array */
        return [
            'menu'           => $menu->post->post_name,
            'page'           => $page->title,
            'uuid'           => $node->uuid,
            'parent_id'      => $node->parent_id,
            'sort_order'     => $node->sort_order,
            'item_id'        => $node->id,
            'type'           => $node->type->value, // valid or throw
            'title'          => $node->title,
            'prices'         => (string)$node->meta->prices,
            'image_ids'      => (string)$node->meta->image_ids,
            'is_new'         => self::toBoolean( $node->meta->hasTag( 'new' ) )->value,
            'is_vegan'       => self::toBoolean( $node->meta->hasTag( 'vegan' ) )->value,
            'is_vegetarian'  => self::toBoolean( $node->meta->hasTag( 'vegetarian' ) )->value,
            'is_glutensmart' => self::toBoolean( $node->meta->hasTag( 'gluten-smart' ) )->value,
            'is_organic'     => self::toBoolean( $node->meta->hasTag( 'organic' ) )->value,
            'description'    => $node->description,
        ];

    }
}