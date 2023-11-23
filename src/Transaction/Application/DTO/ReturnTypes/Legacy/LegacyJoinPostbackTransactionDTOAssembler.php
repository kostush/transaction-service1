<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

interface LegacyJoinPostbackTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param ChargeTransaction $transaction Transaction
     * @return LegacyJoinPostbackCommandHttpDTO
     */
    public function assemble(
        ChargeTransaction $transaction
    ): LegacyJoinPostbackCommandHttpDTO;
}
