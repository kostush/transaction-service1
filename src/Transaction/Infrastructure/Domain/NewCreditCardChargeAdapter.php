<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface NewCreditCardChargeAdapter extends ChargeAdapterInterface
{
    /**
     * @param ChargeWithNewCreditCardCommand $command     The command
     * @param \DateTimeImmutable             $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function charge(
        ChargeWithNewCreditCardCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
