<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;

interface EpochJoinPostbackTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from provided data
     *
     * @param Transaction         $transaction    Transaction object
     * @param EpochBillerResponse $billerResponse EpochBillerResponse object
     * @return EpochJoinPostbackCommandHttpDTO
     */
    public function assemble(
        Transaction $transaction,
        EpochBillerResponse $billerResponse
    ): EpochJoinPostbackCommandHttpDTO;
}
