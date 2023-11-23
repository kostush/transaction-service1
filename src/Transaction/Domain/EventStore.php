<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

interface EventStore
{
    /**
     * @param DomainEvent $aDomainEvent Domain event
     * @return mixed
     */
    public function append(DomainEvent $aDomainEvent);
}
