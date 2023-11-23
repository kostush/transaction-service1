<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

class TransactionHealthQueryHttpDTO implements \JsonSerializable
{
    /** @var array */
    private $transactionHealth;

    /**
     * TransactionHealthQueryHttpDTO constructor.
     * @param array $transactionHealth Transaction health array.
     */
    public function __construct(array $transactionHealth)
    {
        $this->transactionHealth = $transactionHealth;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->transactionHealth;
    }
}
