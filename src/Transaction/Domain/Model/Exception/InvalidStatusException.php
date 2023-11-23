<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class InvalidStatusException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_STATUS_EXCEPTION;
}
