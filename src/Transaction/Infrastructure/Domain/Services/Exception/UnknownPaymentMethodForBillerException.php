<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class UnknownPaymentMethodForBillerException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::UNHANDLED_PAYMENT_METHOD_FOR_BILLER_EXCEPTION;

    /**
     * UnknownPaymentTypeForBillerException constructor.
     *
     * @param string          $paymentMethod Payment type
     * @param string          $billerName    Biller Name
     * @param \Throwable|null $previous      Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $paymentMethod, string $billerName, \Throwable $previous = null)
    {
        parent::__construct($previous, $paymentMethod, $billerName);
    }
}
