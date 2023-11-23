<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use DateTimeImmutable;
use Exception;

class RocketgateCreditCardBillerResponse extends RocketgateBillerResponse
{
    /**
     * @var ?int
     */
    protected $threeDVersion;

    /**
     * @param DateTimeImmutable $requestDate  Request date
     * @param string            $jsonResponse Raw Response
     * @param DateTimeImmutable $responseDate Response date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(
        DateTimeImmutable $requestDate,
        string $jsonResponse,
        DateTimeImmutable $responseDate
    ): self {
        /** @var self $billerResponse */
        $billerResponse = parent::create($requestDate, $jsonResponse, $responseDate);

        return $billerResponse;
    }

    /**
     * @param Exception|null $exception Exception
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public static function createAbortedResponse(?Exception $exception): self
    {
        /** @var self $billerResponse */
        $billerResponse = parent::createAbortedResponse($exception);

        return $billerResponse;
    }
}
