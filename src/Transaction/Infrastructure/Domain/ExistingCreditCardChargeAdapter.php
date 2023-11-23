<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface ExistingCreditCardChargeAdapter extends ChargeAdapterInterface
{
    /**
     * @param ChargeWithExistingCreditCardCommand $command     The command
     * @param \DateTimeImmutable                  $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function charge(
        ChargeWithExistingCreditCardCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
