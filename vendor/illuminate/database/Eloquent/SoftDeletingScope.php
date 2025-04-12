<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent;

class SoftDeletingScope implements \MenuManager\Vendor\Illuminate\Database\Eloquent\Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['Restore', 'RestoreOrCreate', 'CreateOrRestore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder, \MenuManager\Vendor\Illuminate\Database\Eloquent\Model $model)
    {
        $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }
    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
        $builder->onDelete(function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);
            return $builder->update([$column => $builder->getModel()->freshTimestampString()]);
        });
    }
    /**
     * Get the "deleted at" column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getDeletedAtColumn(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        if (\count((array) $builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDeletedAtColumn();
        }
        return $builder->getModel()->getDeletedAtColumn();
    }
    /**
     * Add the restore extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addRestore(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('restore', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->withTrashed();
            return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
        });
    }
    /**
     * Add the restore-or-create extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addRestoreOrCreate(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('restoreOrCreate', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();
            return tap($builder->firstOrCreate($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }
    /**
     * Add the create-or-restore extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addCreateOrRestore(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('createOrRestore', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();
            return tap($builder->createOrFirst($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }
    /**
     * Add the with-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithTrashed(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('withTrashed', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder, $withTrashed = \true) {
            if (!$withTrashed) {
                return $builder->withoutTrashed();
            }
            return $builder->withoutGlobalScope($this);
        });
    }
    /**
     * Add the without-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutTrashed(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('withoutTrashed', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this)->whereNull($model->getQualifiedDeletedAtColumn());
            return $builder;
        });
    }
    /**
     * Add the only-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyTrashed(\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder)
    {
        $builder->macro('onlyTrashed', function (\MenuManager\Vendor\Illuminate\Database\Eloquent\Builder $builder) {
            $model = $builder->getModel();
            $builder->withoutGlobalScope($this)->whereNotNull($model->getQualifiedDeletedAtColumn());
            return $builder;
        });
    }
}
