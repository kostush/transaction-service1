<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class QyssoCCTransactionReturnType extends TransactionReturnType
{
    /**
     * @var string
     */
    private $first6;

    /**
     * @var string
     */
    private $last4;

    /**
     * @var string
     */
    private $cardExpirationYear;

    /**
     * @var string
     */
    private $cardExpirationMonth;

    /**
     * QyssoCCTransactionReturnType constructor.
     * @param string             $transactionId       TransactionId
     * @param string             $first6              First6
     * @param string             $last4               Last4
     * @param string             $amount              Amount
     * @param string             $status              Status
     * @param \DateTimeImmutable $createdAt           CreatedAt
     * @param string             $cardExpirationMonth The card expiration month
     * @param string             $cardExpirationYear  The card expiration month
     * @param null|string        $rebillAmount        RebillAmount
     * @param null|string        $rebillFrequency     RebillFrequency
     * @param null|string        $rebillStart         RebillStart
     */
    private function __construct(
        string $transactionId,
        string $first6,
        string $last4,
        string $amount,
        string $status,
        \DateTimeImmutable $createdAt,
        string $cardExpirationMonth,
        string $cardExpirationYear,
        ?string $rebillAmount,
        ?string $rebillFrequency,
        ?string $rebillStart
    ) {
        $this->transactionId       = $transactionId;
        $this->first6              = $first6;
        $this->last4               = $last4;
        $this->amount              = $amount;
        $this->createdAt           = $createdAt->format('Y-m-d H:i:s');
        $this->cardExpirationMonth = $cardExpirationMonth;
        $this->cardExpirationYear  = $cardExpirationYear;
        $this->rebillAmount        = $rebillAmount;
        $this->rebillFrequency     = $rebillFrequency;
        $this->rebillStart         = $rebillStart;
        $this->status              = $status;
    }

    /**
     * @param Transaction $transaction The Transaction object
     * @return QyssoCCTransactionReturnType
     */
    public static function createFromTransaction(Transaction $transaction)
    {
        $rebillAmount = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->amount()->value() : null;

        $rebillFrequency = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->frequency() : null;

        $rebillStart = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->start() : null;

        return new static(
            (string) $transaction->transactionId(),
            '', //$transaction->paymentInformation()->creditCardNumber()->firstSix(),
            '', //$transaction->paymentInformation()->creditCardNumber()->lastFour(),
            (string) $transaction->chargeInformation()->amount()->value(),
            (string) $transaction->status(),
            $transaction->createdAt(),
            '', //$transaction->paymentInformation()->expirationMonth(),
            '', //$transaction->paymentInformation()->expirationYear(),
            $rebillAmount,
            $rebillFrequency,
            $rebillStart
        );
    }

    /**
     * @return string
     */
    public function last4()
    {
        return $this->last4;
    }

    /**
     * @return string
     */
    public function first6()
    {
        return $this->first6;
    }
}