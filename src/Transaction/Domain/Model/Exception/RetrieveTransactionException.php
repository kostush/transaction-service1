<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class RetrieveTransactionException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::RETRIEVE_TRANSACTION_EXCEPTION;
}
