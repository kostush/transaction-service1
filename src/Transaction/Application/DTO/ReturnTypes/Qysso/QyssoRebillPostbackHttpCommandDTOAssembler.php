<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class QyssoRebillPostbackHttpCommandDTOAssembler implements QyssoRebillPostbackTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @return mixed|QyssoRebillPostbackCommandHttpDTO
     */
    public function assemble(Transaction $transaction): QyssoRebillPostbackCommandHttpDTO
    {
        return new QyssoRebillPostbackCommandHttpDTO($transaction);
    }
}
