<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;

class InvalidTransactionTypeException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_TRANSACTION_TYPE_EXCEPTION;

    /**
     * MissingCreditCardInformationException constructor.
     *
     * @param string         $parameter Missing parameter name
     * @param Throwable|null $previous  Previews exception
     * @throws Exception
     */
    public function __construct(string $parameter, Throwable $previous = null)
    {
        parent::__construct($previous, $parameter);
    }
}
