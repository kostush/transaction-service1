<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;

class AbortTransactionCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * AbortTransactionCommand constructor.
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
