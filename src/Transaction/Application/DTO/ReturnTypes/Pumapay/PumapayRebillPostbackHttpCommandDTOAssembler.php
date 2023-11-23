<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayRebillPostbackHttpCommandDTOAssembler implements PumapayRebillPostbackTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @return mixed|PumapayRebillPostbackCommandHttpDTO
     */
    public function assemble(Transaction $transaction): PumapayRebillPostbackCommandHttpDTO
    {
        return new PumapayRebillPostbackCommandHttpDTO($transaction);
    }
}
