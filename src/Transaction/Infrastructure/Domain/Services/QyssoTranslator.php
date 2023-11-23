<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use ProBillerNG\Qysso\Application\NewSaleResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;

class QyssoTranslator
{
    /**
     * @param NewSaleResponse   $response     Response
     * @param DateTimeImmutable $requestDate  Request Date
     * @param DateTimeImmutable $responseDate Response Date
     * @return QyssoNewSaleBillerResponse
     */
    public function translateNewSale(
        NewSaleResponse $response,
        DateTimeImmutable $requestDate,
        DateTimeImmutable $responseDate
    ): QyssoNewSaleBillerResponse {
        return QyssoNewSaleBillerResponse::create($requestDate, $response, $responseDate);
    }
}
