<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Event;

use ProBillerNG\Transaction\Domain\DomainEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingAggregateIdOnEventException;

abstract class BaseEvent implements DomainEvent
{

    const LATEST_VERSION = 4;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var \DateTimeImmutable
     */
    protected $occurredOn;

    /**
     * @var string
     */
    protected $transactionType;

    const REBILL_UPDATE_TRANSACTION = 'rebill_update_transaction';
    const CHARGE_TRANSACTION        = 'charge_transaction';
    //TODO is will be implemented for free join and rebill start requests
    const AUTH_TRANSACTION = 'auth_transaction';

    /**
     * BaseEvent constructor.
     *
     * @param string                  $aggregateId     Aggregate Id
     * @param \DateTimeImmutable|null $occurredOn      Occurred On
     * @param string|null             $transactionType The issuing class
     * @throws \Exception
     */
    public function __construct(?string $aggregateId, ?\DateTimeImmutable $occurredOn, ?string $transactionType = null)
    {
        $this->aggregateId     = $aggregateId;
        $this->occurredOn      = $occurredOn ?: new \DateTimeImmutable();
        $this->transactionType = $transactionType;
        $this->version         = self::LATEST_VERSION;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTimeImmutable
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @return string
     * @throws MissingAggregateIdOnEventException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function aggregateId(): string
    {
        if (null === $this->aggregateId) {
            throw new MissingAggregateIdOnEventException($this);
        }
        return $this->aggregateId;
    }

    /***
     * @return string
     */
    public function transactionType(): string
    {
        return $this->transactionType;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }
}
