<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;

interface PumapayRetrieveQrCodeAdapter extends PumapayAdapter
{
    /**
     * @param ChargeTransaction $transaction
     * @return PumapayBillerResponse
     */
    public function retrieveQrCode(ChargeTransaction $transaction): PumapayBillerResponse;
}
