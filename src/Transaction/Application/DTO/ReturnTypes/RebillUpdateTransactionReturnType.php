<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RebillUpdateTransactionReturnType extends TransactionReturnType
{
    /** @var array */
    private $paymentDetailedInformation;

    /**
     * RebillUpdateTransactionReturnType constructor.
     * @param string             $transactionId              Transaction Id
     * @param \DateTimeImmutable $createdAt                  Created At
     * @param string             $status                     Status
     * @param string|null        $amount                     The amount
     * @param string|null        $rebillAmount               The rebill amount
     * @param string|null        $rebillStart                The rebill start
     * @param string|null        $rebillFrequency            The rebill frequency
     * @param array              $paymentDetailedInformation Payment info
     */
    private function __construct(
        string $transactionId,
        \DateTimeImmutable $createdAt,
        string $status,
        ?string $amount,
        ?string $rebillAmount,
        ?string $rebillStart,
        ?string $rebillFrequency,
        array $paymentDetailedInformation
    ) {
        $this->transactionId              = $transactionId;
        $this->status                     = $status;
        $this->amount                     = $amount;
        $this->rebillAmount               = $rebillAmount;
        $this->rebillStart                = $rebillStart;
        $this->rebillFrequency            = $rebillFrequency;
        $this->createdAt                  = $createdAt->format('Y-m-d H:i:s');
        $this->paymentDetailedInformation = $paymentDetailedInformation;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RebillUpdateTransactionReturnType
     */
    public static function createFromTransaction(Transaction $transaction)
    {
        $amount          = null;
        $rebillAmount    = null;
        $rebillStart     = null;
        $rebillFrequency = null;

        $chargeInformation = $transaction->chargeInformation();

        // TODO done to align with the new TS
        if ($chargeInformation !== null && $chargeInformation->amount() !== null) {
            $amount = (string) $chargeInformation->amount();
            $rebill = $chargeInformation->rebill();

            if ($rebill !== null) {
                $rebillAmount    = (string) $rebill->amount();
                $rebillStart     = (string) $rebill->start();
                $rebillFrequency = (string) $rebill->frequency();
            }
        }

        return new static(
            (string) $transaction->transactionId(),
            $transaction->createdAt(),
            (string) $transaction->status(),
            $amount,
            $rebillAmount,
            $rebillStart,
            $rebillFrequency,
            self::retrievePaymentDetailedInformation($transaction->paymentInformation())
        );
    }

    /**
     * @param PaymentInformation|null $paymentInformation The payment information
     * @return array
     */
    private static function retrievePaymentDetailedInformation(?PaymentInformation $paymentInformation): array
    {
        if (is_null($paymentInformation)) return [];

        return $paymentInformation->detailedInformation();
    }
}
