<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class InvalidPaymentInformationException extends DomainException implements SensitiveInformationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_PAYMENT_INFORMATION_EXCEPTION;

    /**
     * InvalidBillerResponseException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
