<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayRebillPostbackCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * PumapayRebillPostbackCommandHttpDTO constructor.
     * @param Transaction $transaction Transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'transactionId' => (string) $this->transaction->transactionId(),
            'status'        => (string) $this->transaction->status()
        ];
    }
}
