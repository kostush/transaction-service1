<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class UnknownPaymentTypeForBillerException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::UNHANDLED_PAYMENT_TYPE_FOR_BILLER_EXCEPTION;

    /**
     * UnknownPaymentTypeForBillerException constructor.
     *
     * @param string          $paymentType Payment type
     * @param string          $billerName  Biller Name
     * @param \Throwable|null $previous    Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $paymentType, string $billerName, \Throwable $previous = null)
    {
        parent::__construct($previous, $paymentType, $billerName);
    }
}
