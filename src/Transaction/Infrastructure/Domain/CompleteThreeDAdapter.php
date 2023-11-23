<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface CompleteThreeDAdapter extends ChargeAdapterInterface
{
    /**
     * @param CompleteThreeDCreditCardCommand $command     Complete ThreeD Command
     * @param \DateTimeImmutable              $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function complete(
        CompleteThreeDCreditCardCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
