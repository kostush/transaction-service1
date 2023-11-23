<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

interface DomainEvent
{
    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn(): \DateTimeImmutable;

    /**
     * @return string
     */
    public function aggregateId(): string;
}
