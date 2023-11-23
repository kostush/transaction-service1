<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\TransactionId;

class PerformRocketgateCancelRebillCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $merchantPassword;

    /**
     * @var string
     */
    private $merchantCustomerId;

    /**
     * @var string
     */
    private $merchantInvoiceId;

    /**
     * PerformRocketgateCancelRebillCommand constructor
     * @param string $transactionId      The transaction id
     * @param string $merchantId         The merchant id
     * @param string $merchantPassword   The merchant password
     * @param string $merchantCustomerId The merchant customer
     * @param string $merchantInvoiceId  The merhant invoice id
     */
    public function __construct(
        string $transactionId,
        string $merchantId,
        string $merchantPassword,
        string $merchantCustomerId,
        string $merchantInvoiceId
    ) {
        $this->transactionId      = $transactionId;
        $this->merchantId         = $merchantId;
        $this->merchantPassword   = $merchantPassword;
        $this->merchantCustomerId = $merchantCustomerId;
        $this->merchantInvoiceId  = $merchantInvoiceId;
    }

    /**
     * @return TransactionId
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function merchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function merchantPassword(): string
    {
        return $this->merchantPassword;
    }

    /**
     * @return string
     */
    public function merchantCustomerId(): string
    {
        return $this->merchantCustomerId;
    }

    /**
     * @return string
     */
    public function merchantInvoiceId(): string
    {
        return $this->merchantInvoiceId;
    }
}
