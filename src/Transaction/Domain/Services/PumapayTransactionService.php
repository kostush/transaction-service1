<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

interface PumapayTransactionService
{
    /**
     * @param RetrievePumapayQrCodeCommand $command Command.
     * @return ChargeTransaction
     */
    public function createOrUpdateTransaction(RetrievePumapayQrCodeCommand $command): ChargeTransaction;
}
