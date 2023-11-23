<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

interface QyssoRebillPostbackTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from Transaction aggregate
     *
     * @param Transaction $transaction Transaction object
     * @return QyssoRebillPostbackCommandHttpDTO
     */
    public function assemble(Transaction $transaction): QyssoRebillPostbackCommandHttpDTO;
}
