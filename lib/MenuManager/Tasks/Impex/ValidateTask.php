<?php

namespace MenuManager\Tasks\Impex;

use MenuManager\Model\Impex;
use MenuManager\Model\ImpexAction;
use MenuManager\Model\Job;
use MenuManager\Model\JobStatus;
use MenuManager\Model\Menu;
use MenuManager\Service\Database;
use MenuManager\Tasks\TaskResult;
use MenuManager\Types\NodeType;
use MenuManager\Utils\EnumTools;

class ValidateTask {

    protected $msg = [];

    protected function collect(): array {
        return array_unique( $this->msg );
    }

    protected function rowMessage( Impex $row, string $msg ): void {
        $this->msg[] = $msg . "  Row='" . join( ',', $row->toArray() ) . "'";
    }

    public function run( $job_id ): TaskResult {

        Database::load();

        // ERRORS + WARNINGS

        // create : err : parent_id cannot be set
        // create : err : sort_order cannot be set
        // create : err : item_id cannot be set
        // create : err : title must be set

        // update : err : parent_id cannot be empty
        // update : err : sort_order cannot be empty
        // update : err : item_id cannot be empty
        // update : err : title cannot be empty

        // delete : err : item_id cannot be empty
        // delete : warn : if children then delete will remove children as well


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

//        // guard : menu count
//        $menus = $rows->groupBy( 'menu' );
//        if ( $menus->count() === 0 ) {
//            $this->msg[] = '0 menus in job.';
//            return TaskResult::failure( 'Invalid', $this->collect() );
//        }
//

        // Menu data.
        $menus = array_column( Menu::all(), 'post' );
        $menu_ids = array_merge(
            array_column( $menus, 'ID' ),
            array_column( $menus, 'post_name' )
        );
//        print_r( $menu_ids );


        // Process impex rows one menu at a time.
        $rows->each( function ( $row ) {
            // Generic data validation.
            if ( ! $this->emptyOrEnum( ImpexAction::class, $row->action ) ) {
                $this->rowMessage( $row, "'action' must be empty or one of '" . EnumTools::csv( ImpexAction::class ) . "'." );
            }
            if ( $this->emptyString( $row->menu ) ) {
                $this->rowMessage( $row, "'menu' cannot be empty." );
            }
            if ( $this->emptyString( $row->page ) ) {
                $this->rowMessage( $row, "'page' cannot be empty." );
            }
            if ( ! $this->emptyOrNumeric( $row->parent_id ) ) {
                $this->rowMessage( $row, "'parent_id' must be empty or numeric. Found parent_id='{$row->parent_id}'." );
            }
            if ( ! $this->emptyOrNumeric( $row->sort_order ) ) {
                $this->rowMessage( $row, "'sort_order' must be empty or numeric. Found sort_order='{$row->sort_order}'." );
            }
            if ( ! $this->inEnum( NodeType::class, $row->type ) ) {
                $this->rowMessage( $row, "'type' must be one of '" . EnumTools::csv( NodeType::class ) . "'." );
            }
            if ( ! $this->emptyOrNumeric( $row->item_id ) ) {
                $this->rowMessage( $row, "'item_id' must be empty or numeric. Found item_id='{$row->item_id}'." );
            }
            if ( ! $this->emptyOrPipeDelimited( $row->prices ) ) {
                $this->rowMessage( $row, "'prices' must be empty or a pipe '|' delimited string similar to 'a|b|c'. Found prices='{$row->prices}'." );
            }
            if ( ! $this->emptyOrPipeDelimited( $row->image_ids ) ) {
                $this->rowMessage( $row, "'image_ids' must be empty or a pipe '|' delimited string similar to 'a|b|c'. Found image_ids='{$row->image_ids}'." );
            }
            if ( $this->emptyString( $row->title ) ) {
                $this->rowMessage( $row, "'title' cannot be empty." );
            }


            // Action specific issues... if action empty it's assumed to be 'create'.
            $action = ImpexAction::tryFrom( $row->action ) ?? ImpexAction::Create;

            switch ( $action ) {

                case ImpexAction::Create:
//                    // @todo implement impex action "insert"
//                    Logger::taskInfo( 'modify-menu', 'unhandled row action in impex : ' . ImpexAction::Insert->value );
                    break;

                case ImpexAction::Update:
//                    $this->update( $row );
                    break;
//
                case ImpexAction::Price:
//                    $this->updatePriceOnly( $row );
                    break;
//
                case ImpexAction::Delete:
//                    $this->delete( $root, $row );
                    break;
            }


        } );


        // Invalid
        if ( ! empty( $this->msg ) ) {
            $job->status = JobStatus::Invalid;
            $job->save();
            return TaskResult::failure( "Invalid", $this->collect() );
        }

        // Valid
        $job->status = JobStatus::Invalid;
        $job->save();
        return TaskResult::success( 'Valid' );


    }

//    protected function emptyOrInArray( mixed $v, array $values ): bool {
//        return $v === '' || in_array( $v, $values );
//    }

    protected function emptyString( mixed $v ): bool {
        return is_string( $v ) && (string)$v === '';
    }

    protected function inEnum( string $enum_class, mixed $v ): bool {
        return in_array( $v, EnumTools::values( $enum_class ) );
    }

    protected function emptyOrEnum( string $enum_class, mixed $v ): bool {
        return $v === '' || in_array( $v, EnumTools::values( $enum_class ) );
    }

    protected function emptyOrNumeric( mixed $v ): bool {
        return $v === '' || is_numeric( $v );
    }

    protected function emptyOrPipeDelimited( mixed $v ): bool {
        return preg_match( '/^[^|]+(\|[^|]+)*$/', $v );
    }
}