<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

interface TransactionalSession
{
    /**
     * Executes operation atomically
     *
     * @param callable $operation Operation
     * @return mixed
     */
    public function executeAtomically(callable $operation);

    /**
     * Flush the entity manager
     *
     * @return void
     */
    public function flush();
}
