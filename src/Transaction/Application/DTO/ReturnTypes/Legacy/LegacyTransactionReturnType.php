<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use DateTimeImmutable;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class LegacyTransactionReturnType extends TransactionReturnType
{
    /** @var string|null */
    protected $legacyTransactionId;

    /** @var string|null */
    protected $legacyMemberId;

    /** @var string|null */
    protected $legacySubscriptionId;

    /**
     * TransactionPayloadCreditCardType constructor.
     * @param string            $transactionId   TransactionId
     * @param string            $amount          Amount
     * @param string            $status          Status
     * @param DateTimeImmutable $createdAt       CreatedAt
     * @param string|null       $rebillAmount    RebillAmount
     * @param string|null       $rebillFrequency RebillFrequency
     * @param string|null       $rebillStart     RebillStart
     * @param string|null       $legacyTransactionId
     * @param string|null       $legacyMemberId
     * @param string|null       $legacySubscriptionId
     */
    private function __construct(
        string $transactionId,
        string $amount,
        string $status,
        DateTimeImmutable $createdAt,
        ?string $rebillAmount,
        ?string $rebillFrequency,
        ?string $rebillStart,
        ?string $legacyTransactionId,
        ?string $legacyMemberId,
        ?string $legacySubscriptionId
    ) {
        $this->transactionId        = $transactionId;
        $this->amount               = $amount;
        $this->createdAt            = $createdAt->format('Y-m-d H:i:s');
        $this->rebillAmount         = $rebillAmount;
        $this->rebillFrequency      = $rebillFrequency;
        $this->rebillStart          = $rebillStart;
        $this->status               = $status;
        $this->legacyTransactionId  = $legacyTransactionId;
        $this->legacyMemberId       = $legacyMemberId;
        $this->legacySubscriptionId = $legacySubscriptionId;
    }

    /**
     * @param Transaction $transaction The Transaction object
     * @return LegacyTransactionReturnType
     */
    public static function createFromTransaction(Transaction $transaction): LegacyTransactionReturnType
    {
        $rebillAmount = ($transaction->chargeInformation()->rebill() !== null) ?
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
            $rebillAmount,
            $rebillFrequency,
            $rebillStart,
            $transaction->legacyTransactionId(),
            $transaction->legacyMemberId(),
            $transaction->legacySubscriptionId()
        );
    }
}
