<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateLookupThreeDsTwoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;

class RocketgateLookupThreeDsTwoTranslator
{
    /**
     * @param string             $response     Lookup response
     * @param \DateTimeImmutable $requestDate  Request date
     * @param \DateTimeImmutable $responseDate Response date
     * @return BillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function toRocketgateLookupBillerResponse(
        string $response,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): BillerResponse {
        Log::info('Creating lookup 3ds2 response from Rocketgate response');

        try {
            return RocketgateLookupThreeDsTwoBillerResponse::create($requestDate, $response, $responseDate);
        } catch (\Exception $e) {
            throw new InvalidBillerResponseException();
        }
    }
}
