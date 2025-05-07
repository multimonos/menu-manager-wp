<?php

namespace MenuManager\Admin\AdminPages\Export\Actions;

use MenuManager\Admin\Service\NoticeService;
use MenuManager\Admin\Types\AdminFormAction;
use MenuManager\Admin\Util\FormActionHelper;
use MenuManager\Model\Menu;
use MenuManager\Tasks\Menu\ExportTask;
use MenuManager\Types\Export\ExportConfig;
use MenuManager\Types\Export\ExportContext;
use MenuManager\Types\Export\ExportFormat;
use MenuManager\Utils\Splitter;

class ExportCustomAction implements AdminFormAction {

    protected $filters = [
        ['name' => 'Page', 'field' => 'page'],
        ['name' => 'UUID', 'field' => 'uuid'],
        ['name' => 'Parent', 'field' => 'parent_id'],
        ['name' => 'Type', 'field' => 'type'],
        ['name' => 'ID', 'field' => 'item_id'],
        ['name' => 'Image', 'field' => 'image_ids'],
        ['name' => 'Title', 'field' => 'title'],
    ];

    public function id(): string {
        return 'mm_export_customd';
    }

    public function name(): string {
        return __( 'Export', 'menu-manager' );
    }

    public function register(): void {
        FormActionHelper::registerHandler( $this );
    }

    public function form(): string {
        $menus = Menu::all();
        ob_start();
        ?>
        <style type="text/css">
            #export-custom input::placeholder {
                color: #ccc;
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
                    <p><strong>Format</strong></p>
                    <ul class="export-filters">
                        <li>
                            <label><input type="radio" name="format" value="<?php echo ExportFormat::Csv->value; ?>" checked="checked"> CSV</label>
                        </li>
                        <li>
                            <label><input type="radio" name="format" value="<?php echo ExportFormat::Excel->value; ?>"> Excel</label>
                        </li>
                    </ul>
                </fieldset>

                <fieldset>
                    <p><strong>Menus</strong></p>
                    <ul class="export-filters">
                        <li>
                            <p><label><input type="radio" name="menu_filter" value="<?php echo ExportConfig::ALL_MENUS; ?>" checked="checked"> All</label></p>
                        </li>
                        <li>
                            <p><label><input type="radio" name="menu_filter" value="ids"> Specific menus only:</label></p>
                        </li>

                        <ul id="menu-filter" class="export-filters">
                            <li>
                                <label>
                                    <?php foreach ( $menus as $menu ): ?>
                                        <p><label><input type="checkbox" name="menus[]" value="<?php echo $menu->id; ?>"> <?php echo $menu->post->post_title; ?></label></p>
                                    <?php endforeach; ?>
                                </label>
                            </li>
                        </ul>
                    </ul>
                </fieldset>


                <fieldset>
                    <p><strong>Item Filters</strong></p>
                    <ul id="item-filters" class="export-filters">
                        <?php foreach ( $this->filters as $filter ) : ?>
                            <li>
                                <label><span class="label-responsive"><?php echo $filter['name']; ?></span></label>
                                <input type="text" name="filters[<?php echo $filter['field']; ?>]" value="" placeholder="Comma separated list of values"/>
                            </li>
                        <?php endforeach; ?>
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
                    $( 'input[name="menus[]"]' ).each( function ( idx, el ) {
                        $( this ).prop( 'checked', false )
                    } )
                }
            }

            $( 'input[name=menu_filter]' ).on( 'click', resetMenusFilter )

            // ensure if child clicked then parent selected for menus filter
            $( 'input[name="menus[]"' ).on( 'click', function ( e ) {
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

        if ( isset( $_POST['menu_filter'] ) && $_POST['menu_filter'] === 'ids' && empty( $_POST['menus'] ?? [] ) ) {
            NoticeService::errorRedirect( 'Menu is required.', wp_get_referer() );

        }

        // Export config.
        $config = new ExportConfig();
        $config->context = ExportContext::Download;
        $config->format = ExportFormat::from( $_POST['format'] ?? ExportFormat::Csv->value );
        $config->menus = $_POST['menu_filter'] === 'ids'
            ? array_filter( $_POST['menus'] )
            : [ExportConfig::ALL_MENUS];

        // Filters
        $filters = array_filter( $_POST['filters'] ?? [] );
        foreach ( $filters as $field => $value ) {
            $arr = Splitter::unique( trim( $value ) );
            if ( ! empty( $arr ) ) {
                $config->filterBy( $field, $arr );
            }
        }
        $a = 1;

        // Task
        $task = new ExportTask();
        $task->run( $config );
    }
}