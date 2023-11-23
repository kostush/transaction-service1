<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use ProbillerNG\LegacyServiceClient\Api\DefaultApi;
use ProbillerNG\LegacyServiceClient\ApiException;
use ProbillerNG\LegacyServiceClient\Model\ErrorInformation;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlRequest;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlResponse;
use ProBillerNG\Logger\Log;

class LegacyNewSaleClient
{
    /**
     * @var DefaultApi
     */
    private $legacyService;

    /**
     * LegacyNewSaleClient constructor.
     * @param DefaultApi $legacyService Legacy Service.
     */
    public function __construct(DefaultApi $legacyService)
    {
        $this->legacyService = $legacyService;
    }

    /**
     * @param string                     $siteId  Site Id.
     * @param GeneratePurchaseUrlRequest $request Request.
     * @return ErrorInformation|GeneratePurchaseUrlResponse
     * @throws ApiException
     * @throws \Exception
     */
    public function generatePurchaseUrl(string $siteId, GeneratePurchaseUrlRequest $request)
    {
        $contentType = 'application/json';

        return $this->legacyService->generatePurchaseUrlV1(
            $siteId,
            $contentType,
            Log::getSessionId(),
            Log::getCorrelationId(),
            $request
        );
    }
}
