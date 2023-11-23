<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

class LegacyJoinPostbackHttpCommandDTOAssembler implements LegacyJoinPostbackTransactionDTOAssembler
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @return LegacyJoinPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function assemble(ChargeTransaction $transaction): LegacyJoinPostbackCommandHttpDTO
    {
        return new LegacyJoinPostbackCommandHttpDTO($transaction);
    }
}
