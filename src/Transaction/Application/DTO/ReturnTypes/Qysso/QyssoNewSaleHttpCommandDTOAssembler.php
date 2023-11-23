<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

class QyssoNewSaleHttpCommandDTOAssembler implements QyssoNewSaleTransactionDTOAssembler
{
    /**
     * @param array $transactions Transactions array
     * @return QyssoNewSaleCommandHttpDTO
     */
    public function assemble(
        array $transactions
    ): QyssoNewSaleCommandHttpDTO {
        return new QyssoNewSaleCommandHttpDTO($transactions);
    }
}
