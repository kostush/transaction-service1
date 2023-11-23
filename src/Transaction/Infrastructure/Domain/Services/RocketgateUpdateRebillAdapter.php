<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\UpdateRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\UpdateRebillAdapter as BaseUpdateRebillAdapter;

class RocketgateUpdateRebillAdapter extends BaseUpdateRebillAdapter implements UpdateRebillAdapter
{
    /**
     * @param UpdateRebillCommand $updateRebillCommand Rocketgate Update Rebill Command
     * @param \DateTimeImmutable  $requestDate         Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws Exception\RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function start(
        UpdateRebillCommand $updateRebillCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->start($updateRebillCommand);
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

    /**
     * @param UpdateRebillCommand $updateRebillCommand Rocketgate Update Rebill Command
     * @param \DateTimeImmutable  $requestDate         Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws Exception\RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function stop(
        UpdateRebillCommand $updateRebillCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->stop($updateRebillCommand);
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

    /**
     * @param UpdateRebillCommand $updateRebillCommand Rocketgate Update Rebill Command
     * @param \DateTimeImmutable  $requestDate         Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws Exception\RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function update(
        UpdateRebillCommand $updateRebillCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->update($updateRebillCommand);
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
