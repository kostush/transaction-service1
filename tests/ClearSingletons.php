<?php

declare(strict_types=1);

namespace Tests;

use ProBillerNG\Transaction\Domain\DomainEventPublisher;

trait ClearSingletons
{
    /**
     * Clear all singletons for the purpose of avoiding contamination
     * @return void
     */
    public function clearSingleton()
    {
        DomainEventPublisher::tearDown();
    }
}