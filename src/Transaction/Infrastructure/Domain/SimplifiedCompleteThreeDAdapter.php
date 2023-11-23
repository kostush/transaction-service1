<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use DateTimeImmutable;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface SimplifiedCompleteThreeDAdapter extends ChargeAdapterInterface
{
    /**
     * @param SimplifiedCompleteThreeDCommand $command     Simplified Complete ThreeD Command
     * @param DateTimeImmutable               $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function simplifiedComplete(
        SimplifiedCompleteThreeDCommand $command,
        DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
