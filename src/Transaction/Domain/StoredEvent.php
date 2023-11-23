<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

use ProBillerNG\Transaction\Domain\Model\Event\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\EventId;

class StoredEvent extends BaseEvent
{
    /**
     * @var EventId
     */
    protected $eventId;

    /**
     * @var string
     */
    protected $eventBody;

    /**
     * @var string
     */
    protected $typeName;

    /**
     * @param EventId            $eventId      EventId
     * @param string             $aggregateId  Aggregate Id
     * @param string             $aTypeName    Type Name
     * @param \DateTimeImmutable $anOccurredOn Occurred on
     * @param string             $anEventBody  Event body
     * @throws \Exception
     */
    public function __construct(
        EventId $eventId,
        string $aggregateId,
        string $aTypeName,
        \DateTimeImmutable $anOccurredOn,
        string $anEventBody
    ) {
        parent::__construct($aggregateId, $anOccurredOn);
        $this->eventId     = $eventId;
        $this->aggregateId = $aggregateId;
        $this->eventBody   = $anEventBody;
        $this->typeName    = $aTypeName;
    }

    /**
     * @return EventId
     */
    public function eventId(): EventId
    {
        return $this->eventId;
    }

    /**
     * @return string
     */
    public function eventBody(): string
    {
        return $this->eventBody;
    }

    /**
     * @return string
     */
    public function typeName(): string
    {
        return $this->typeName;
    }
}
