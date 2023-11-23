<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;

class RocketgateCreditCardChargeTranslator
{
    /**
     * @param string            $responsePayload Raw Biller Response
     * @param DateTimeImmutable $requestDate     Request date
     * @param DateTimeImmutable $responseDate    Response date
     * @return RocketgateCreditCardBillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function toCreditCardBillerResponse(
        string $responsePayload,
        DateTimeImmutable $requestDate,
        DateTimeImmutable $responseDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Creating charge response from Rocketgate response');

        try {
            return RocketgateCreditCardBillerResponse::create($requestDate, $responsePayload, $responseDate);
        } catch (Exception $e) {
            throw new InvalidBillerResponseException();
        }
    }
}
