<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\NewCreditCardChargeAdapter;

class RocketgateNewCreditCardChargeAdapter extends ChargeAdapter implements NewCreditCardChargeAdapter
{
    /**
     * Execute Rocketgate charge
     *
     * @param ChargeWithNewCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     * @param \DateTimeImmutable             $requestDate             Request date
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    public function charge(
        ChargeWithNewCreditCardCommand $rocketgateChargeCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->chargeNewCreditCard($rocketgateChargeCommand);
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
