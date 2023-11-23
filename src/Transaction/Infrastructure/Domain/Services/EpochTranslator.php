<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochPostbackBillerResponse;

class EpochTranslator
{
    /**
     * @param string             $response     Response
     * @param \DateTimeImmutable $requestDate  Request Date
     * @param \DateTimeImmutable $responseDate Response Date
     * @return EpochBillerResponse
     */
    public function translate(
        string $response,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): EpochBillerResponse {
        return EpochPostbackBillerResponse::create($requestDate, $response, $responseDate);
    }

    /**
     * @param string             $response     Response
     * @param \DateTimeImmutable $requestDate  Request Date
     * @param \DateTimeImmutable $responseDate Response Date
     * @return EpochBillerResponse
     */
    public function translateNewSale(
        string $response,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): EpochBillerResponse {
        return EpochNewSaleBillerResponse::create($requestDate, $response, $responseDate);
    }
}
