<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\SuspendRebillAdapter;

class RocketgateSuspendRebillAdapter extends ChargeAdapter implements SuspendRebillAdapter
{
    /**
     * Execute Rocketgate Suspend Rebill
     *
     * @param SuspendRebillCommand $rocketgateSuspendRebillCommand Rocketgate Suspend Rebill Command
     * @param \DateTimeImmutable   $requestDate                    Request date
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    public function suspend(
        SuspendRebillCommand $rocketgateSuspendRebillCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->suspendRebill($rocketgateSuspendRebillCommand);
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
