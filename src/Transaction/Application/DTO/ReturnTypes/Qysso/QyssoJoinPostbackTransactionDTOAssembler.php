<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

interface QyssoJoinPostbackTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param Transaction $transaction Transaction object
     *
     * @return QyssoJoinPostbackCommandHttpDTO
     */
    public function assemble(
        Transaction $transaction
    ): QyssoJoinPostbackCommandHttpDTO;
}
