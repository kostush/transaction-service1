<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class RocketgateCompleteThreeDCreditCardAdapter extends ChargeAdapter implements CompleteThreeDAdapter
{
    /**
     * @param CompleteThreeDCreditCardCommand $rocketgateCompleteThreeDCommand Rocketgate Complete ThreeD Command
     * @param \DateTimeImmutable              $requestDate                     Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws Exception\RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function complete(
        CompleteThreeDCreditCardCommand $rocketgateCompleteThreeDCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->completeThreeD($rocketgateCompleteThreeDCommand);
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
