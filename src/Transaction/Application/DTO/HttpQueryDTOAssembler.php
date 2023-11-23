<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateTransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class HttpQueryDTOAssembler implements RocketgateTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @return TransactionQueryHttpDTO
     */
    public function assemble(Transaction $transaction): TransactionQueryHttpDTO
    {
        if ($transaction instanceof ChargeTransaction) {
            return new ChargeTransactionQueryHttpDTO($transaction);
        }

        if ($transaction instanceof RebillUpdateTransaction) {
            return new RebillUpdateTransactionQueryHttpDTO($transaction);
        }
    }
}
