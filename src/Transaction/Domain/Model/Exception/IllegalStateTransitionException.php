<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class IllegalStateTransitionException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::ILLEGAL_STATE_TRANSITION_EXCEPTION;

    /**
     * IllegalStateTransitionException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
