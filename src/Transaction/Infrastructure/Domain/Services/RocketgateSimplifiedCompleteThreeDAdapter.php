<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\SimplifiedCompleteThreeDAdapter;

class RocketgateSimplifiedCompleteThreeDAdapter extends ChargeAdapter implements SimplifiedCompleteThreeDAdapter
{

    /**
     * @param SimplifiedCompleteThreeDCommand $command     Command
     * @param DateTimeImmutable               $requestDate Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws InvalidBillerResponseException
     * @throws RocketgateServiceException
     * @throws Exception
     */
    public function simplifiedComplete(
        SimplifiedCompleteThreeDCommand $command,
        DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->simplifiedCompleteThreeD($command);
        $responseDate = new DateTimeImmutable();

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
