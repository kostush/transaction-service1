<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Qysso\Application\NewSaleResponse;

class QyssoNewSaleBillerResponse extends QyssoBillerResponse
{
    /**
     * @param int                $result          Result
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Request Payload
     * @param \DateTimeImmutable $requestDate     Request Date
     * @param string|null        $responsePayload Response Payload
     * @param \DateTimeImmutable $responseDate    Response Date
     */
    protected function __construct(
        int $result,
        string $code,
        string $reason,
        ?string $requestPayload,
        \DateTimeImmutable $requestDate,
        ?string $responsePayload,
        \DateTimeImmutable $responseDate
    ) {
        parent::__construct($result, $code, $reason, $requestPayload, $requestDate, $responsePayload, $responseDate);
    }

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param NewSaleResponse    $response
     * @param \DateTimeImmutable $responseDate Response date
     * @return self
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        NewSaleResponse $response,
        \DateTimeImmutable $responseDate
    ): self {
        $responseArray = $response->toArray();

        return new static(
            static::mapStatus($responseArray['code']),
            $responseArray['code'],
            $responseArray['reason'],
            json_encode($responseArray['request']),
            $requestDate,
            json_encode($responseArray['response']),
            $responseDate
        );
    }
}
