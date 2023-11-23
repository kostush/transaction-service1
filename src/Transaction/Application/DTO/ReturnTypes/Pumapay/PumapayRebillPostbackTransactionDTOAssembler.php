<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

interface PumapayRebillPostbackTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from Transaction aggregate
     *
     * @param Transaction $transaction Transaction object
     * @return PumapayRebillPostbackCommandHttpDTO
     */
    public function assemble(Transaction $transaction): PumapayRebillPostbackCommandHttpDTO;
}
