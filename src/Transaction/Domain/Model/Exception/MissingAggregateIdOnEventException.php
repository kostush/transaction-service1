<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\Event\BaseEvent;

class MissingAggregateIdOnEventException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::AGGREGATE_ID_MISSING;

    /**
     * MissingAggregateIdOnEventException constructor.
     *
     * @param BaseEvent       $event    Event
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(BaseEvent $event, \Throwable $previous = null)
    {
        parent::__construct($previous, get_class($event));
    }
}
