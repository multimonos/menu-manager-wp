<?php

namespace MenuManager\Admin\AdminPages\Export\Actions;

use MenuManager\Admin\Types\AdminFormAction;
use MenuManager\Admin\Util\FormActionHelper;
use MenuManager\Admin\Util\GetActionHelper;
use MenuManager\Model\Menu;

class ExportCustomAction implements AdminFormAction {

    public function id(): string {
        return 'mm_export_customd_csv';
    }

    public function name(): string {
        return __( 'Export', 'menu-manager' );
    }

    public function register(): void {
        GetActionHelper::registerHandler( $this );

        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Menu::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 10, 2 );
    }

    public function form(): string {
        $menus = Menu::all();
        ob_start();
        ?>
        <style type="text/css">
            #export-custom input::placeholder {
                color: #ababab;
            }

            #menu-filter {
                margin-left: 2rem;
            }

            #item-filters label {
                width: 3rem !important;
                display: inline-block;
            }

            #item-filters input {
                width: 80%;
            }
        </style>
        <div class="card" id="export-custom">
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
                <p>Define criteria for a more custom export.</p>

                <fieldset>
                    <p><strong>Item Filters</strong></p>
                    <ul id="item-filters" class="export-filters">
                        <li>
                            <label><span class="label-responsive">UUID</span></label>
                            <input type="text" name="filters[uuid]" value="" placeholder="CSV of values"/>
                        </li>
                        <li>
                            <label><span class="label-responsive">Type</span></label>
                            <input type="text" name="filters[type]" value="" placeholder="Match any string"/>
                        </li>
                    </ul>
                </fieldset>

                <fieldset>
                    <p><strong>Menus</strong></p>
                    <ul class="export-filters">
                        <li>
                            <p><label><input type="radio" name="menu_filter" value="all" checked="checked"> All</label></p>
                        </li>
                        <li>
                            <p><label><input type="radio" name="menu_filter" value="ids"> Specific menus only:</label></p>
                        </li>

                        <ul id="menu-filter" class="export-filters">
                            <li>
                                <label>
                                    <?php foreach ( $menus as $menu ): ?>
                                        <p><label><input type="checkbox" name="menu_ids[]" value="<?php echo $menu->id; ?>"> <?php echo $menu->post->post_title; ?></label></p>
                                    <?php endforeach; ?>
                                </label>
                            </li>
                        </ul>
                    </ul>
                </fieldset>

                <fieldset>
                    <p><strong>Format</strong></p>
                    <ul class="export-filters">
                        <li>
                            <label><input type="radio" name="format" value="csv" checked="checked"> CSV</label>
                        </li>
                        <li>
                            <label><input type="radio" name="format" value="excel"> Excel</label>
                        </li>
                    </ul>
                </fieldset>

                <div style="margin-top:2rem;">
                    <?php echo FormActionHelper::requiredFields( $this ); ?>
                </div>
            </form>
        </div>
        <script>
        jQuery( function ( $ ) {
            function resetMenusFilter( e ) {
                $el = $( this )
                if ( $el.is( ':checked' ) && $el.val() === 'all' ) {
                    $( 'input[name="menu_ids[]"]' ).each( function ( idx, el ) {
                        $( this ).prop( 'checked', false )
                    } )
                }
            }


            $( 'input[name=menu_filter]' ).on( 'click', resetMenusFilter )

            // ensure if child clicked then parent selected for menus filter
            $( 'input[name="menu_ids[]"' ).on( 'click', function ( e ) {
                console.log( 'clicked' )
                $( 'input[name=menu_filter][value=ids]' ).prop( 'checked', true );
            } )
        } );
        </script>
        <?php
        return ob_get_clean();
    }

    public function handle(): void {
        FormActionHelper::validateOrRedirect( $this, wp_get_referer() );

        die( 'export custom' );
        // Validate
//        GetActionHelper::validateOrFail( $this );

        // Get model.
//        $menu = GetActionHelper::findPostOrRedirect( Menu::class );

        // export
//        $path = "menu-export_{$menu->post->post_name}_{$menu->post->ID}__" . date( 'Ymd\THis' ) . '.csv';
//        $task = new ExportMenuAsCsvTask();
//        $rs = $task->run( ExportMethod::Download, $menu, $path );
//        exit;
    }
}