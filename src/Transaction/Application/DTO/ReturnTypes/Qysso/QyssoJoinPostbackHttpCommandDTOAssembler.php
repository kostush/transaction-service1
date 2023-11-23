<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class QyssoJoinPostbackHttpCommandDTOAssembler implements QyssoJoinPostbackTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction object
     *
     * @return QyssoJoinPostbackCommandHttpDTO
     */
    public function assemble(
        Transaction $transaction
    ): QyssoJoinPostbackCommandHttpDTO {
        return new QyssoJoinPostbackCommandHttpDTO($transaction);
    }
}
