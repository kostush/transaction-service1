<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Query;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochNewSaleCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStartUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStopUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateSuspendCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\MakeNetbillingSuspendCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\MakeNetbillingUpdateRebillCommand;

class RetrieveTransactionHealthQuery extends Query
{
    private $billerCommandMappings = [
        'rocketgate' => [
            MakeRocketgateChargeCommand::class,
            MakeRocketgateSuspendCommand::class,
            MakeRocketgateStartUpdateRebillCommand::class,
            MakeRocketgateStopUpdateRebillCommand::class,
            MakeRocketgateUpdateRebillCommand::class,
            MakeRocketgateCompleteThreeDCommand::class,
        ],
        'netbilling' => [
            MakeNetbillingSuspendCommand::class,
            MakeNetbillingSuspendCommand::class,
            MakeNetbillingUpdateRebillCommand::class
        ],
        'epoch' => [
            EpochNewSaleCommand::class
        ]
    ];

    /**
     * @return array
     */
    public function billerCommandMappings(): array
    {
        return $this->billerCommandMappings;
    }
}
