<?php

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayCancelRebillCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var Transaction
     */
    protected $transaction;


    /**
     * PumapayPostbackCommandHttpDTO constructor.
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
