<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Transaction\Domain\Model\Exception\SensitiveInformationException;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class InvalidPaymentInformationException extends InfrastructureException implements SensitiveInformationException
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
