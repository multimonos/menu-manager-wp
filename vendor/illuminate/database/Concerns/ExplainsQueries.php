<?php

namespace MenuManager\Vendor\Illuminate\Database\Concerns;

use MenuManager\Vendor\Illuminate\Support\Collection;
trait ExplainsQueries
{
    /**
     * Explains the query.
     *
     * @return \Illuminate\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        $explanation = $this->getConnection()->select('EXPLAIN ' . $sql, $bindings);
        return new Collection($explanation);
    }
}
