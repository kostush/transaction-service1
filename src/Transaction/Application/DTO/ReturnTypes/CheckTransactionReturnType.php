<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class CheckTransactionReturnType extends TransactionReturnType
{
    /**
     * CheckTransactionReturnType constructor.
     *
     * @param string             $transactionId   Transaction Id.
     * @param string             $amount          Amount.
     * @param string             $status          Status.
     * @param \DateTimeImmutable $createdAt       Created At.
     * @param string|null        $rebillAmount    Rebill Amount.
     * @param string|null        $rebillFrequency Rebill Frequency.
     * @param string|null        $rebillStart     Rebill Start.
     */
    private function __construct(
        string $transactionId,
        string $amount,
        string $status,
        \DateTimeImmutable $createdAt,
        ?string $rebillAmount,
        ?string $rebillFrequency,
        ?string $rebillStart
    ) {
        $this->transactionId   = $transactionId;
        $this->amount          = $amount;
        $this->createdAt       = $createdAt->format('Y-m-d H:i:s');
        $this->rebillAmount    = $rebillAmount;
        $this->rebillFrequency = $rebillFrequency;
        $this->rebillStart     = $rebillStart;
        $this->status          = $status;
    }

    /**
     * @param Transaction $transaction transaction object
     *
     * @return CheckTransactionReturnType
     */
    public static function createFromTransaction(Transaction $transaction)
    {
        $rebillValue = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->amount()->value() : null;

        $rebillFrequency = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->frequency() : null;

        $rebillStart = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->start() : null;

        return new static(
            (string) $transaction->transactionId(),
            (string) $transaction->chargeInformation()->amount()->value(),
            (string) $transaction->status(),
            $transaction->createdAt(),
            $rebillValue,
            $rebillFrequency,
            $rebillStart
        );
    }
}
