<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayRetrieveQrCodeBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;

class PumapayTranslator
{
    /**
     * @param string             $responsePayload Raw Biller Response
     * @param \DateTimeImmutable $requestDate     Request date
     * @param \DateTimeImmutable $responseDate    Response date
     * @return PumapayRetrieveQrCodeBillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function toRetrieveQrCodeBillerResponse(
        string $responsePayload,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): PumapayBillerResponse {
        Log::info('Creating retrieve QR code response from Pumapay');

        try {
            return PumapayRetrieveQrCodeBillerResponse::create($requestDate, $responsePayload, $responseDate);
        } catch (\Exception $e) {
            throw new InvalidBillerResponseException($e);
        }
    }

    /**
     * @param string             $response     Response
     * @param \DateTimeImmutable $requestDate  Request Date
     * @param \DateTimeImmutable $responseDate Response Date
     * @return PumapayBillerResponse
     */
    public function translate(
        string $response,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): PumapayBillerResponse {
        return PumapayPostbackBillerResponse::create($requestDate, $response, $responseDate);
    }

    /**
     * @param string             $response     Response
     * @param \DateTimeImmutable $requestDate  Request date
     * @param \DateTimeImmutable $responseDate Response date
     * @return PumapayCancelRebillBillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function toCancelRebillBillerResponse(
        string $response,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): PumapayBillerResponse {
        try {
            return PumapayCancelRebillBillerResponse::create(
                $requestDate,
                $response,
                $responseDate
            );
        } catch (\Exception $e) {
            throw new InvalidBillerResponseException($e);
        }
    }
}
