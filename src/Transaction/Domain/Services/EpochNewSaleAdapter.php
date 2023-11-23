<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Epoch\Application\Services\NewSaleCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;

interface EpochNewSaleAdapter extends EpochAdapter
{
    /**
     * @param NewSaleCommand $newSaleCommand New Sale Epoch Command
     * @return EpochNewSaleBillerResponse
     */
    public function newSale(
        NewSaleCommand $newSaleCommand
    ): EpochNewSaleBillerResponse;
}
