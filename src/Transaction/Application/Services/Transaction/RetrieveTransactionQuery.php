<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Query;

class RetrieveTransactionQuery extends Query
{
    /** @var string */
    private $transactionId;

    /**
     * RetrieveTransactionQuery constructor.
     * @param string $transactionId Transaction Id
     */
    public function __construct(
        string $transactionId
    ) {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }
}
