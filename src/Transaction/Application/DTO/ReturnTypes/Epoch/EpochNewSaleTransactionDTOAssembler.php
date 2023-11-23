<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;

interface EpochNewSaleTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param array $transactions Transactions array
     * @return EpochNewSaleCommandHttpDTO
     */
    public function assemble(
        array $transactions
    ): EpochNewSaleCommandHttpDTO;
}
