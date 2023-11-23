<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class RocketgateExistingCreditCardChargeAdapter extends ChargeAdapter implements ExistingCreditCardChargeAdapter
{
    /**
     * Execute Rocketgate charge
     *
     * @param ChargeWithExistingCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     * @param \DateTimeImmutable                  $requestDate             Request date
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    public function charge(
        ChargeWithExistingCreditCardCommand $rocketgateChargeCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->chargeExistingCreditCard($rocketgateChargeCommand);
        $responseDate = new \DateTimeImmutable();

        // Call the translator
        $billerResponse = $this->translator->toCreditCardBillerResponse(
            $response,
            $requestDate,
            $responseDate
        );

        // return result
        return $billerResponse;
    }
}
