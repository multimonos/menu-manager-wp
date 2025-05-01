<?php

namespace MenuManager\Admin\AdminPages\Backup;

use MenuManager\Admin\AdminPages\Backup\Actions\DeleteBackupAction;
use MenuManager\Admin\AdminPages\Backup\Actions\RestoreBackupAction;
use MenuManager\Model\Backup;
use MenuManager\Service\Database;

class BackupListTable extends \WP_List_Table {

    protected RestoreBackupAction $restoreAction;
    protected DeleteBackupAction $deleteAction;

    public function __construct( $args = array() ) {
        parent::__construct( $args );

        $this->restoreAction = new RestoreBackupAction();
        $this->deleteAction = new DeleteBackupAction();
    }

    function prepare_items() {
        // Core list definition.
        $this->_column_headers = [$this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns()];

        // Initialize items.
        Database::load();
        $items = Backup::query();

        if ( isset( $_GET['created_at'] ) ) {
            $items->orderBy( 'created_at', $_GET['order'] ?? 'desc' );
        } else {
            $items->orderBy( 'id', $_GET['order'] ?? 'desc' );

        }
        $this->items = $items->get()->all();
    }

    function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'filename'   => 'File',
            'id'         => 'ID',
            'created_at' => 'Created At',
        ];
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_sortable_columns() {
        return [
            'id'         => ['id', false],
            'created_at' => ['created_at', false],
        ];
    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item->id );
    }

    function column_filename( $item ) {
        $actions = [
            'restore' => $this->restoreAction->link( $item ),
            'delete'  => $this->deleteAction->link( $item ),
        ];

        return esc_html( $item->filename ) . ' BackupListTable.php' . $this->row_actions( $actions );
    }

    function column_id( $item ) {
        return $item->id;
    }

    function column_created_at( $item ) {
        return $item->created_at;
    }
}
