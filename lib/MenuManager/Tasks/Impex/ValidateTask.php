<?php

namespace MenuManager\Tasks\Impex;

use MenuManager\Model\Impex;
use MenuManager\Model\Job;
use MenuManager\Model\JobStatus;
use MenuManager\Model\Menu;
use MenuManager\Model\Types\ImpexAction;
use MenuManager\Model\Types\NodeType;
use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;
use MenuManager\Utils\EnumTools;
use MenuManager\Utils\ImpexValidator;
use MenuManager\Utils\Splitter;
use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;


class ValidateTask {

    protected $msg = [];

    protected function collect(): array {
        return array_unique( $this->msg );
    }

    protected function rowMessage( Impex $row, string $msg ): void {
        $this->msg[] = $msg . "  Row='" . join( ',', $row->toArray() ) . "'";
    }

    public function rowstr( Impex $row ): string {
        return "Row='" . join( ',', $row->toArray() ) . "'.";
    }

    public function run( $job_id ): TaskResult {

        Database::load();

        // ERRORS + WARNINGS

        // create : err : parent_id cannot be set
        // create : err : sort_order cannot be set
        // create : err : item_id cannot be set
        // create : err : title must be set

        // throw no errors
        $err = [];
        $msg = [];

        // guard : job
        $job = Job::find( $job_id );

        if ( $job === null ) {
            return TaskResult::failure( "Record not found {$job_id}." );
        }

        // Impex rows
        $rows = $job->impexes;

        // guard : row count
        if ( $rows->isEmpty() ) {
            $this->msg[] = '0 rows in job.';
            return TaskResult::failure( 'Invalid', $this->collect() );
        }

        // Menu data.
        $menus = array_column( Menu::all(), 'post' );
        $menu_ids = array_merge(
            array_column( $menus, 'ID' ),
            array_column( $menus, 'post_name' )
        );

        // Image Ids : pre-collect the subset of valid image_ids to create a hash lookup.
        $image_ids = $this->getValidImageIds( $rows );
        $image_lookup = array_flip( $image_ids ); // isset is fast

        // Loop dependencies.
        $validator = new ImpexValidator();
        $node_types = EnumTools::csv( NodeType::class );
        $impex_actions = EnumTools::csv( ImpexAction::class );


        // Process impex rows one menu at a time.
        $rows->each( function ( $row ) use ( $validator, $node_types, $impex_actions, $image_lookup ) {
            // Row as string for user feedback.
            $rowstr = $this->rowstr( $row );

            // Generic data validation.
            $validator->assertEmptyOrEnum( "'action' must be one of '{$impex_actions}'. Found action='{$row->action}'. {$rowstr}", $row->action, ImpexAction::class );
            $validator->assertNotEmptyString( "'menu' cannot be empty. {$rowstr}", $row->menu );
            $validator->assertNotEmptyString( "'page' cannot be empty. {$rowstr}", $row->page );
            $validator->assertEmptyOrId( "'parent_id' must be empty or a number greater than zero. Found parent_id='{$row->parent_id}'. {$rowstr}", $row->parent_id );
            $validator->assertEmptyOrPositveInteger( "'sort_order' must be empty or number greater than zero. Found sort_order='{$row->sort_order}'. {$rowstr}", $row->sort_order );
            $validator->assertEnum( "'type' must be one of '{$node_types}'. Found type='{$row->type}'. {$rowstr}", $row->type, NodeType::class );
            $validator->assertEmptyOrId( "'item_id' must be empty or a number greater than zero. Found item_id='{$row->item_id}'. {$rowstr}", $row->item_id );
            $validator->assertEmptyOrPipeDelimited( "'prices' must be empty or a pipe '|' delimited string similar to 'a|b|c'. Found prices='{$row->prices}'. {$rowstr}", $row->prices );
            $validator->assertEmptyOrPipeDelimited( "'image_ids' must be empty or a pipe '|' delimited string similar to 'a|b|c'. Found image_ids='{$row->image_ids}'. {$rowstr}", $row->image_ids );
            $validator->assertNotEmptyString( "'title' cannot be empty." . $rowstr, $row->title );

            // More complex.
            $validator->assertImageIdsValid( "Invalid image ids. Found ids=[%s]. {$rowstr}", $row->image_ids, $image_lookup );


            // Action specific issues... if action empty it's assumed to be 'create'.
            $action = ImpexAction::tryFrom( $row->action ) ?? ImpexAction::Create;

            switch ( $action ) {

                case ImpexAction::Create:
                    $validator->assertEmptyString( sprintf( "Action '%s' requires 'item_id' to be empty. ", ImpexAction::Create->value ) . $rowstr, $row->item_id );
                    $validator->assertEmptyString( sprintf( "Action '%s' requires 'parent_id' to be empty. ", ImpexAction::Create->value ) . $rowstr, $row->parent_id );
                    $validator->assertEmptyString( sprintf( "Action '%s' requires 'sort_order' to be empty. ", ImpexAction::Create->value ) . $rowstr, $row->sort_order );
                    break;

                case ImpexAction::Update:
                    $validator->assertId( sprintf( "Action '%s' requires 'item_id'. ", ImpexAction::Update->value ) . $rowstr, $row->item_id );
                    $validator->assertId( sprintf( "Action '%s' requires 'parent_id'. ", ImpexAction::Update->value ) . $rowstr, $row->parent_id );
                    $validator->assertNotEmptyString( sprintf( "Action '%s' requires 'sort_order'. ", ImpexAction::Create->value ) . $rowstr, $row->sort_order );
                    break;

                case ImpexAction::Price:
                    $validator->assertId( sprintf( "Action '%s' requires 'item_id'. ", ImpexAction::Price->value ) . $rowstr, $row->item_id );
                    break;

                case ImpexAction::Delete:
                    $validator->assertId( sprintf( "Action '%s' requires 'item_id'. ", ImpexAction::Delete->value ) . $rowstr, $row->item_id );
                    break;
            }
        } );


        // Invalid
        if ( ! $validator->isValid() ) {
            $job->status = JobStatus::Invalid;
            $job->save();
            return TaskResult::failure( "Invalid", $validator->getErrors( true ) );
        }

        // Valid
        $job->status = JobStatus::Invalid;
        $job->save();
        return TaskResult::success( 'Valid' );


    }

    protected function getValidImageIds( Collection $rows ): array {
        $ids = $rows->pluck( 'image_ids' )
            ->transform( fn( $x ) => Splitter::split( $x, '|' ) )
            ->flatten()
            ->unique()
            ->map( 'absint' )
            ->toArray();

        $valid_ids = get_posts( [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post__in'       => $ids,
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        return $valid_ids;
    }

}