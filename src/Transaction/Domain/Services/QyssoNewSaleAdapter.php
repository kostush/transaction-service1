<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Qysso\Application\NewSaleCommand;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;

interface QyssoNewSaleAdapter extends EpochAdapter
{
    /**
     * @param NewSaleCommand      $newSaleCommand New Sale Epoch Command
     * @param QyssoBillerSettings $billerSettings
     * @return QyssoNewSaleBillerResponse
     */
    public function newSale(
        NewSaleCommand $newSaleCommand,
        QyssoBillerSettings $billerSettings
    ): QyssoNewSaleBillerResponse;
}
