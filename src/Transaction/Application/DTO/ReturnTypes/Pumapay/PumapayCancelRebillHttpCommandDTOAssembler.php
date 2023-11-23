<?php

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayCancelRebillHttpCommandDTOAssembler implements PumapayCancelRebillDTOAssembler
{
    /**
     * @param Transaction $transaction The transaction entity
     * @return PumapayCancelRebillCommandHttpDTO
     */
    public function assemble(Transaction $transaction): PumapayCancelRebillCommandHttpDTO
    {
        return new PumapayCancelRebillCommandHttpDTO($transaction);
    }
}
