<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class AbortCommandHttpDTO implements \JsonSerializable
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
            'status' => (string) $this->transaction->status()
        ];
    }
}
