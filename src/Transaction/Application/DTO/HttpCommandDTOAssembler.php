<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateTransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class HttpCommandDTOAssembler implements RocketgateTransactionDTOAssembler
{
    /**
     * {@inheritdoc}
     *
     * @param Transaction              $transaction         Transaction
     * @param ErrorClassification|null $errorClassification Error classification for declined transactions
     *
     * @return TransactionCommandHttpDTO
     */
    public function assemble(
        Transaction $transaction,
        ?ErrorClassification $errorClassification = null
    ): TransactionCommandHttpDTO {
        return new TransactionCommandHttpDTO($transaction, $errorClassification);
    }
}
