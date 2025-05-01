<?php

namespace MenuManager\Admin\Actions;

use MenuManager\Admin\Types\AdminPostLinkAction;
use MenuManager\Model\Job;
use MenuManager\Model\Post;
use MenuManager\Task\JobRunTask;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Model;

class JobRunAction implements AdminPostLinkAction {

//    public $mode = 'std';
    public $mode = 'ajax';

    public function id(): string {
        return 'mm_job_run';
    }

    public function register(): void {
        // Register post action handler.
        if ( $this->mode === 'std' ) {
            add_action( 'admin_post_' . $this->id(), [$this, 'handle'] );
        }
        if ( $this->mode === 'ajax' ) {
            add_action( 'wp_ajax_' . $this->id(), [$this, 'handle'] );
        }

        // Add link to post row.
        add_filter( 'post_row_actions', function ( $actions, $post ) {
            return Job::isType( $post )
                ? $actions + [$this->id() => $this->link( $post )]
                : $actions;
        }, 9999, 2 );

        // Enqueue script.
        add_action( 'admin_enqueue_scripts', [$this, 'enqueueScripts'], 10, 1 );
    }

    public function link( Model|Post|\WP_Post $post ): string {

        $nonce = wp_create_nonce( $this->id() . '_' . $post->ID );

        if ( $this->mode === 'std' ) {

            $url = admin_url( add_query_arg( [
                'action'   => $this->id(),
                'post_id'  => $post->ID,
                '_wpnonce' => $nonce,
            ], 'admin-post.php' ) );

            return sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                $url,
                esc_attr( sprintf( __( 'Run "%s"', 'menu-manager' ), $post->post_title ) ),
                __( 'Run', 'menu-manager' )
            );

        }
        if ( $this->mode === 'ajax' ) {
            return sprintf(
                '<a href="#" class="job-run-action__link" data-post-id="%d" data-nonce="%s">Run</a>',
                $post->ID,
                esc_attr( $nonce )
            );
        }
    }

    public function enqueueScripts( $hook ) {
        if ( 'edit.php' !== $hook ) {
            return;
        }

        if ( $this->mode === 'ajax' ) {

            wp_enqueue_script(
                $this->id(),
                MENU_MANAGER_URL . '/js/' . $this->id() . '.js',
                ['jquery'],
                null,
                true
            );

            wp_localize_script( $this->id(),
                'jobRunActionData',
                [
                    'nonce'    => wp_create_nonce( $this->id() . '_nonce' ),
                    'ajaxUrl'  => admin_url( add_query_arg( [
//                        'XDEBUG_SESSION_START' => 'PHPSTORM',
                    ], 'admin-ajax.php' ) ),
                    'actionId' => $this->id(),
                ]
            );
        }
    }

    public function handle(): void {
        if ( $this->mode === 'std' ) {
            error_log( 'hit standard' );
            // Check if user is allowed
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'menu-manager' ) );
            }
            // Verify parameters
            if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
                wp_die( __( 'Missing required parameters.', 'menu-manager' ) );
            }
            // Verify nonce
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->id() . '_' . $_GET['post_id'] ) ) {
                wp_die( __( 'Security check failed.', 'menu-manager' ) );
            }
            // Get the job
            $job = Job::find( intval( $_GET['post_id'] ) );
            if ( $job === null ) {
                wp_die( __( 'Menu not found.', 'menu-manager' ) );
            }


            error_log( 'job: ' . $job->id . '--' . $job->post->post_title );

            $task = new JobRunTask();
            echo 'ok';
        }

        if ( $this->mode === 'ajax' ) {
            // Check if user is allowed
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( ['message' => 'You do not have sufficient permission to perform this action.'] );
            }
            // Verify parameters
            if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['_wpnonce'] ) ) {
                wp_send_json_error( ['message' => 'Missing required parameters.'] );
            }
            // Verify nonce
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], $this->id() . '_' . $_POST['post_id'] ) ) {
                wp_send_json_error( ['message' => 'Security check failed.'] );
            }

            // Get the job
            $post_id = intval( $_REQUEST['post_id'] );
            $job = Job::find( $post_id );
            if ( $job === null ) {
                wp_send_json_error( ['message' => "Job #{$post_id} not found."] );
            }

            // run
            $task = new JobRunTask();
            $task->run( $job->id );
            wp_send_json_success( ['message' => "Job #{$job->id} complete."] );
        }
    }
}