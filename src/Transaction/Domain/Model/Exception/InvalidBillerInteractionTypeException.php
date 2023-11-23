<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class InvalidBillerInteractionTypeException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::BILLER_INTERACTION_TYPE_INVALID;

    /**
     * InvalidBillerInteractionTypeException constructor.
     * @param string     $type     Biller Interaction type
     * @param \Throwable $previous Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $type, \Throwable $previous = null)
    {
        parent::__construct($previous, $type);
    }
}
