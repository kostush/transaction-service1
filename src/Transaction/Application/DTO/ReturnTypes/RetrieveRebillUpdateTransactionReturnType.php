<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveRebillUpdateTransactionReturnType extends RetrieveTransactionReturnType
{

    /** @var int */
    private $cardExpirationYear;

    /** @var int */
    private $cardExpirationMonth;

    /** @var string */
    protected $previousTransactionId;

    /**
     * RetrieveRebillUpdateTransactionReturnType constructor.
     * @param string                $billerId              Biller Id
     * @param string                $billerName            Biller Name
     * @param string                $merchantId            Merchant Id
     * @param string                $merchantPassword      Merchant Password
     * @param string|null           $invoiceId             Invoice Id
     * @param string|null           $customerId            Customer Id
     * @param string|null           $transactionId         Transaction Id
     * @param string                $cardHash              CardHash
     * @param TransactionReturnType $transaction           Transaction
     * @param int|null              $cardExpirationYear    Card Expiration Year
     * @param int|null              $cardExpirationMonth   Card Expiration Month
     * @param string|null           $previousTransactionId The previous transaction id
     */
    private function __construct(
        string $billerId,
        string $billerName,
        string $merchantId,
        string $merchantPassword,
        ?string $invoiceId,
        ?string $customerId,
        ?string $transactionId,
        ?string $cardHash,
        TransactionReturnType $transaction,
        ?int $cardExpirationYear,
        ?int $cardExpirationMonth,
        ?string $previousTransactionId
    ) {
        $this->billerId              = $billerId;
        $this->billerName            = $billerName;
        $this->merchantId            = $merchantId;
        $this->merchantPassword      = $merchantPassword;
        $this->invoiceId             = $invoiceId;
        $this->customerId            = $customerId;
        $this->billerTransactionId   = $transactionId;
        $this->cardHash              = $cardHash;
        $this->transaction           = $transaction;
        $this->cardExpirationYear    = $cardExpirationYear;
        $this->cardExpirationMonth   = $cardExpirationMonth;
        $this->previousTransactionId = $previousTransactionId;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RetrieveRebillUpdateTransactionReturnType
     */
    public static function createFromEntity(
        Transaction $transaction
    ): RetrieveRebillUpdateTransactionReturnType {
        $transactionPayload = RebillUpdateTransactionReturnType::createFromTransaction($transaction);

        $billerInteractionFields = self::getBillerInteractionFields($transaction->billerInteractions());

        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            $transaction->billerChargeSettings()->merchantId(),
            $transaction->billerChargeSettings()->merchantPassword(),
            $transaction->billerChargeSettings()->merchantInvoiceId(),
            $transaction->billerChargeSettings()->merchantCustomerId(),
            $billerInteractionFields['billerTransactionId'],
            $billerInteractionFields['cardHash'],
            $transactionPayload,
            self::retrieveCardExpirationYear($transaction->paymentInformation()),
            self::retrieveCardExpirationMonth($transaction->paymentInformation()),
            (string) $transaction->previousTransactionId()
        );
    }

    private static function retrieveCardExpirationYear(?PaymentInformation $paymentInformation)
    {
        if (is_null($paymentInformation)) {
            return null;
        }

        if (!method_exists($paymentInformation, 'expirationYear')) {
            return null;
        }

        return $paymentInformation->expirationYear();
    }

    private static function retrieveCardExpirationMonth(?PaymentInformation $paymentInformation)
    {
        if (is_null($paymentInformation)) {
            return null;
        }

        if (!method_exists($paymentInformation, 'expirationMonth')) {
            return null;
        }

        return $paymentInformation->expirationMonth();
    }
}
