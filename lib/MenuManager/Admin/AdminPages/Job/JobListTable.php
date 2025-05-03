<?php

namespace MenuManager\Admin\AdminPages\Job;

use MenuManager\Admin\AdminPages\Job\Actions\DeleteJobAction;
use MenuManager\Admin\AdminPages\Job\Actions\DownloadJobAction;
use MenuManager\Admin\AdminPages\Job\Actions\RunJobAction;
use MenuManager\Admin\Util\DateHelper;
use MenuManager\Model\Job;
use MenuManager\Service\Database;
use MenuManager\Utils\UserHelper;

class JobListTable extends \WP_List_Table {

    protected RunJobAction $runAction;
    protected DeleteJobAction $deleteAction;
    protected DownloadJobAction $downloadAction;

    public function __construct( $args = array() ) {
        parent::__construct( $args );
        $this->runAction = new RunJobAction();
        $this->deleteAction = new DeleteJobAction();
        $this->downloadAction = new DownloadJobAction();
    }

    function prepare_items() {
        // Core list definition.
        $this->_column_headers = [$this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns()];

        // Initialize items.
        Database::load();
        $items = Job::query();

        if ( isset( $_GET['created_at'] ) ) {
            $items->orderBy( 'created_at', $_GET['order'] ?? 'desc' );
        } else if ( isset( $_GET['lastrun_at'] ) ) {
            $items->orderBy( 'lastrun_at', $_GET['order'] ?? 'desc' );
        } else if ( isset( $_GET['created_by'] ) ) {
            $items->orderBy( 'created_by', $_GET['order'] ?? 'desc' );
        } else if ( isset( $_GET['lastrun_by'] ) ) {
            $items->orderBy( 'lastrun_by', $_GET['order'] ?? 'desc' );
        } else {
            $items->orderBy( 'id', $_GET['order'] ?? 'desc' );

        }

        // Set array.
        $this->items = $items->get()->all();
    }

    function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'id'         => 'ID',
            'title'      => 'File',
            'lastrun_at' => 'Last Run',
            'created_at' => 'Created',
        ];
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_sortable_columns() {
        return [
            'id'         => ['id', false],
            'lastrun_at' => ['lastrun_at', false],
            'created_at' => ['created_at', false],
        ];
    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item->id );
    }

    function column_title( $item ) {
        $actions = [
            'run'      => $this->runAction->link( $item ),
            'delete'   => $this->deleteAction->link( $item ),
            'download' => $this->downloadAction->link( $item ),
        ];

        return '<strong><a class="row-title"> ' . esc_html( $item->title ) . '</a></strong><div style="white-space: nowrap;">' . $this->row_actions( $actions ) . '</div>';
    }

    function column_id( $item ) {
        return $item->id;
    }

    function column_created_at( $item ) {
        return DateHelper::format( $item->created_at ) . '<br/><em class="lighter">by ' . UserHelper::emailOrUnknown( $item->created_by ) . '</em>';
    }

    function column_lastrun_at( $item ) {
        return DateHelper::format( $item->lastrun_at ) . '<br/><em class="lighter">by ' . UserHelper::emailOrUnknown( $item->lastrun_by ) . '</em>';
    }

}
