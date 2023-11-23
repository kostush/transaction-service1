<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;

interface PumapayCancelRebillAdapter extends PumapayAdapter
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $businessId  Business Id
     * @param string            $apiKey      Api Key
     * @return PumapayBillerResponse
     */
    public function cancelRebill(
        ChargeTransaction $transaction,
        string $businessId,
        string $apiKey
    ): PumapayBillerResponse;
}
