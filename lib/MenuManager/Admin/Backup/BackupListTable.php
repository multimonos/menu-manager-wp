<?php

namespace MenuManager\Admin\Backup;

use MenuManager\Model\Backup;
use MenuManager\Service\Database;

class BackupListTable extends \WP_List_Table {

    protected RestoreBackupAction $restoreAction;
    protected $deleteAction;

    public function __construct( $args = array() ) {
        parent::__construct( $args );

        $this->restoreAction = new RestoreBackupAction();


    }

    function get_columns() {
        return [
            'cb'       => '<input type="checkbox" />',
            'filename' => 'File',
            'created'  => 'Created At',
        ];
    }

    function prepare_items() {
        Database::load();

        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

        $this->_column_headers = [
            $columns,
            $hidden,
            $sortable,
        ];

        $models = Backup::all()->all();
        $this->items = $models;

    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item->id );
    }

    function column_filename( $item ) {
        $actions = [
            'restore' => $this->restoreAction->link( $item ),
            'delete'  => sprintf( '<a href="?page=%s&action=delete&id=%d" onclick="return confirm(\'Are you sure?\')">Delete</a>', esc_attr( $_REQUEST['page'] ), $item->id ),
        ];

        return esc_html( $item->filename ) . ' ' . $this->row_actions( $actions );
    }

    function column_created( $item ) {
        return $item->created_at;
    }


    // Add other column_* functions as needed
}
