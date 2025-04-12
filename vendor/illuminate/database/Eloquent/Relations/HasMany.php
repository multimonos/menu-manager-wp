<?php

namespace MenuManager\Vendor\Illuminate\Database\Eloquent\Relations;

use MenuManager\Vendor\Illuminate\Database\Eloquent\Collection;
class HasMany extends \MenuManager\Vendor\Illuminate\Database\Eloquent\Relations\HasOneOrMany
{
    /**
     * Convert the relationship to a "has one" relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function one()
    {
        return \MenuManager\Vendor\Illuminate\Database\Eloquent\Relations\HasOne::noConstraints(fn() => new \MenuManager\Vendor\Illuminate\Database\Eloquent\Relations\HasOne($this->getQuery(), $this->parent, $this->foreignKey, $this->localKey));
    }
    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return !\is_null($this->getParentKey()) ? $this->query->get() : $this->related->newCollection();
    }
    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }
        return $models;
    }
    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchMany($models, $results, $relation);
    }
}
