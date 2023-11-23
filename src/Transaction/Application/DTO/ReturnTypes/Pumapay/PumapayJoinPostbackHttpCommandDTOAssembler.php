<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayJoinPostbackHttpCommandDTOAssembler implements PumapayJoinPostbackTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @return mixed|PumapayJoinPostbackCommandHttpDTO
     */
    public function assemble(Transaction $transaction): PumapayJoinPostbackCommandHttpDTO
    {
        return new PumapayJoinPostbackCommandHttpDTO($transaction);
    }
}
