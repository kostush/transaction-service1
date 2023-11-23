<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidCreditCardNumberException extends InvalidPayloadException implements SensitiveInformationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_CREDIT_CARD_INFORMATION_EXCEPTION;

    /**
     * InvalidCreditCardNumberException constructor.
     *
     * @param string          $parameter Missing parameter name
     * @param \Throwable|null $previous  Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $parameter, \Throwable $previous = null)
    {
        parent::__construct($previous, $parameter);
    }
}
