<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface SuspendRebillAdapter extends ChargeAdapterInterface
{
    /**
     * @param SuspendRebillCommand $command     Suspend Rebill Command
     * @param \DateTimeImmutable   $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function suspend(
        SuspendRebillCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
