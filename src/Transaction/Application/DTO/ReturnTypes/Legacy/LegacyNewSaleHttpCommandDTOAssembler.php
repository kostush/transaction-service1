<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

class LegacyNewSaleHttpCommandDTOAssembler implements LegacyNewSaleTransactionDTOAssembler
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @return LegacyNewSaleCommandHttpDTO
     * @throws \Exception
     */
    public function assemble(
        ChargeTransaction $transaction
    ): LegacyNewSaleCommandHttpDTO {
        return new LegacyNewSaleCommandHttpDTO($transaction);
    }
}
