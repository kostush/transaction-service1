<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;

class InvalidPaymentMethodException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_PAYMENT_METHOD_EXCEPTION;

    /**
     * MissingChargeInformationException constructor.
     *
     * @param string          $parameter Missing parameter name
     * @param \Throwable|null $previous  Previews exception
     * @throws Exception
     */
    public function __construct(string $parameter, \Throwable $previous = null)
    {
        parent::__construct($previous, $parameter);
    }
}
