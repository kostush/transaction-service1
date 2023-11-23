<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

interface DomainEventSubscriber
{
    /**
     * @param DomainEvent $event Domain event
     * @return mixed
     */
    public function handle(DomainEvent $event);

    /**
     * @param mixed $event Event
     * @return bool
     */
    public function isSubscribedTo($event): bool;
}
