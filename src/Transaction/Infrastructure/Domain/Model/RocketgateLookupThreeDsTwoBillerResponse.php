<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

class RocketgateLookupThreeDsTwoBillerResponse extends RocketgateBillerResponse
{
    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return RocketgateLookupThreeDsTwoBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): self {
        return parent::create($requestDate, $jsonResponse, $responseDate);
    }

    /**
     * @param \Exception|null $exception Exception
     *
     * @return RocketgateLookupThreeDsTwoBillerResponse
     * @throws \Exception
     */
    public static function createAbortedResponse(?\Exception $exception): self
    {
        return parent::createAbortedResponse($exception);
    }
}
