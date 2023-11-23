<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Pumapay\Code as PumapayCode;

class PumapayCancelRebillBillerResponse extends PumapayBillerResponse
{
    /**
     * @var array
     */
    protected static $return400PumapayCodes = [PumapayCode::PUMAPAY_BAD_REQUEST];

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return PumapayBillerResponse
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): PumapayBillerResponse {
        $decodedResponse = json_decode($jsonResponse, true);

        return new static(
            self::CHARGE_RESULT_APPROVED,
            $decodedResponse['code'],
            (string) $decodedResponse['reason'],
            json_encode($decodedResponse['request']),
            $requestDate,
            json_encode($decodedResponse['response']),
            $responseDate
        );
    }
}
