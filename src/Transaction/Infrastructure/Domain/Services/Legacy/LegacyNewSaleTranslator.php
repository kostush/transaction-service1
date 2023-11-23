<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use DateTimeImmutable;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlRequest;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlResponse;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\LegacyServiceResponseException;

class LegacyNewSaleTranslator
{
    /**
     * @param mixed|GeneratePurchaseUrlResponse $response         Response
     * @param GeneratePurchaseUrlRequest        $request          Request
     * @param DateTimeImmutable                 $requestDateTime  Request date
     * @param DateTimeImmutable                 $responseDateTime Response date
     * @return LegacyNewSaleBillerResponse
     * @throws Exception
     * @throws LegacyServiceResponseException
     */
    public function translate(
        $response,
        GeneratePurchaseUrlRequest $request,
        DateTimeImmutable $requestDateTime,
        DateTimeImmutable $responseDateTime
    ): LegacyNewSaleBillerResponse {

        if (!$response instanceof GeneratePurchaseUrlResponse) {
            throw new LegacyServiceResponseException();
        }

        return LegacyNewSaleBillerResponse::createSuccessResponse(
            $response->getRedirectUrl(),
            (string) $response,
            (string) $request,
            $requestDateTime,
            $responseDateTime
        );
    }
}
