<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class QyssoJoinPostbackCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
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
            'status'        => (string) $this->transaction->status(),
            'paymentType'   => (string) $this->transaction->paymentType(),
            'paymentMethod' => (string) $this->transaction->paymentMethod(),
        ];
    }
}
