<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

class EpochNewSaleHttpCommandDTOAssembler implements EpochNewSaleTransactionDTOAssembler
{
    /**
     * @param array         $transactions    Transactions array
     * @return EpochNewSaleCommandHttpDTO
     */
    public function assemble(
        array $transactions
    ): EpochNewSaleCommandHttpDTO {
        return new EpochNewSaleCommandHttpDTO($transactions);
    }
}
