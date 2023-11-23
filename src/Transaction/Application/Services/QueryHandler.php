<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

interface QueryHandler
{
    /**
     * Executes a query
     *
     * @param Query $query Query
     * @return mixed
     */
    public function execute(Query $query);
}
