<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class AbortHttpCommandDTOAssembler implements AbortTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @return mixed|AbortCommandHttpDTO
     */
    public function assemble(Transaction $transaction): AbortCommandHttpDTO
    {
        return new AbortCommandHttpDTO($transaction);
    }
}
