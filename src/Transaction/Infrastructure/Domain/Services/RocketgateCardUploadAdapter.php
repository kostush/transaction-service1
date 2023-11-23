<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\CardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class RocketgateCardUploadAdapter extends ChargeAdapter implements CardUploadAdapter
{
    /**
     * @param CardUploadCommand  $rocketgateCardUploadCommand Rocketgate Complete ThreeD Command
     * @param \DateTimeImmutable $requestDate                 Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws Exception\RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function cardUpload(
        CardUploadCommand $rocketgateCardUploadCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        // Call rocketgate handler
        $response     = $this->client->cardUpload($rocketgateCardUploadCommand);
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
