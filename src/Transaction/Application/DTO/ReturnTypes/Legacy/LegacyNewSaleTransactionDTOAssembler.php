<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

interface LegacyNewSaleTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param ChargeTransaction $transactions Transactions array
     * @return LegacyNewSaleCommandHttpDTO
     */
    public function assemble(
        ChargeTransaction $transactions
    ): LegacyNewSaleCommandHttpDTO;
}
