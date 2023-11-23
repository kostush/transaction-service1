<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;

interface QyssoNewSaleTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param array $transactions Transactions array
     * @return QyssoNewSaleCommandHttpDTO
     */
    public function assemble(
        array $transactions
    ): QyssoNewSaleCommandHttpDTO;
}
