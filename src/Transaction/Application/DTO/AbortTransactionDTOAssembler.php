<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Domain\Model\Transaction;

interface AbortTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from Transaction aggregate
     *
     * @param Transaction $transaction Transaction object
     * @return AbortCommandHttpDTO
     */
    public function assemble(Transaction $transaction): AbortCommandHttpDTO;
}
