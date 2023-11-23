<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ErrorException;
use Probiller\Common\BillerMapping;
use Probiller\Common\BillerMappingFilters;
use Probiller\Common\BillerUnifiedGroupErrorResponse;
use Probiller\Common\Fields\RocketgateFieldsQuery;
use Probiller\Service\Config\GetBillerMappingRequest;
use Probiller\Service\Config\GetBillerUnifiedGroupErrorRequest;
use Probiller\Service\Config\ProbillerConfigClient;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingUnifiedGroupError;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateUnifiedGroupError;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteria;
use ProBillerNG\Transaction\Domain\Model\ServiceClient;
use ProBillerNG\Transaction\Infrastructure\Application\Services\AzureActiveDirectoryAccessToken;
use const Grpc\STATUS_OK;

class ConfigServiceClient extends ServiceClient
{
    /**
     * @var ProbillerConfigClient
     */
    protected $configClient;

    /**
     * @param ProbillerConfigClient $configClient
     */
    public function __construct(ProbillerConfigClient $configClient)
    {
        $this->configClient = $configClient;
    }

    /**
     * @param MappingCriteria $mappingCriteria
     *
     * @return BillerUnifiedGroupErrorResponse|null
     * @throws Exception|ErrorException
     */
    public function retrieveBillerUnifiedGroupError(MappingCriteria $mappingCriteria): ?BillerUnifiedGroupErrorResponse
    {
        Log::info(
            'Starting retrieval of biller group error from config service',
            [
                'billerName'  => $mappingCriteria->billerName(),
                'initialData' => json_encode($mappingCriteria->toArray())
            ]
        );

        switch ($mappingCriteria->billerName()) {
            case BillerSettings::ROCKETGATE:
                $fields = (new RocketgateUnifiedGroupError())
                    ->setReasonCode($mappingCriteria->reasonCode())
                    ->setBankResponseCode($mappingCriteria->bankResponseCode())
                    ->generateMapField();

                break;
            case BillerSettings::NETBILLING:
                $fields = (new NetbillingUnifiedGroupError())
                    ->setProcessor($mappingCriteria->processor())
                    ->setAuthMessage($mappingCriteria->authMessage())
                    ->generateMapField();

                break;
            default:
                return null;
        }

        $request = new GetBillerUnifiedGroupErrorRequest();
        $request->setMappingCriteria($fields);

        Log::info('Preparing BillerUnifiedGroupError request', ['request' => $request->serializeToString()]);

        $response = $this->configClient->GetBillerUnifiedGroupError($request, $this->addHeaders())->wait();

        /** @var BillerUnifiedGroupErrorResponse $unifiedErrorResponse */
        [$unifiedErrorResponse, $responseStatus] = $response;

        if ($responseStatus->code == STATUS_OK) {
            Log::info('BillerUnifiedGroupError successful response', ['response' => $unifiedErrorResponse->serializeToString()]);

            return $unifiedErrorResponse;
        }

        Log::debug('BillerUnifiedGroupError unsuccessful response',
            [
                'billerName' => $mappingCriteria->billerName(),
                'response'   => is_null($unifiedErrorResponse) ? 'Unified group error response unavailable' : $unifiedErrorResponse->serializeToString(),
                'details'    => $responseStatus->details
            ]
        );

        return null;
    }

    /**
     * This method retrieves filtered biller mapping from config service.
     * The filters applied, are as follows:
     * Biller name, Site Id, Currency, MerchantId
     *
     * @param string $siteId
     * @param string $currency
     * @param string $merchantId
     *
     * @return BillerMapping|null
     * @throws Exception
     */
    public function retrieveRocketgateBillerMapping(string $siteId, string $currency, string $merchantId): ?BillerMapping
    {
        if ($merchantId == '1390920700') {
            // This is order to fallback to an inactive account that we have the new merchant password.
            Log::warning("Fallback to Paysites CAD biller account");
            $response = $this->configClient->GetBillerMappingConfig(
                (new GetBillerMappingRequest())->setBillerMappingId('57124eda-f8df-11e8-8eb2-f2801f1b9fd2'),
                $this->addHeaders()
            )->wait();

            [$billerMappingResponse, $responseStatus] = $response;

            if ($responseStatus->code == STATUS_OK) {
                return $billerMappingResponse;
            }

            Log::warning(
                "RetrieveBillerMapping Could not retrieve biller mapping from config service for the fallback" .
                $responseStatus->details
            );
        }

        $rocketGateFieldsQuery = new RocketgateFieldsQuery();
        $rocketGateFieldsQuery->setMerchantId($merchantId);

        $request = new BillerMappingFilters();
        $request->setSiteId($siteId);
        $request->setCurrency($currency);
        $request->setBillerName(BillerSettings::ROCKETGATE);
        $request->setRocketgate($rocketGateFieldsQuery);

        $response = $this->configClient->GetBillerMappingConfigFiltered($request, $this->addHeaders())->wait();

        [$billerMappingResponse, $responseStatus] = $response;

        if ($responseStatus->code == STATUS_OK) {
            return $billerMappingResponse;
        }

        Log::warning(
            "RetrieveBillerMapping Could not retrieve biller mapping from config service! Details: " . $responseStatus->details,
            [
                'siteId'     => $request->getSiteId(),
                'billerName' => $request->getBillerName(),
                'merchantId' => $request->getRocketgate()->getMerchantId(),
                'currency'   => $request->getCurrency(),
            ]
        );

        return null;
    }

    /**
     * @return array
     *
     * @throws Exception
     * @throws \Exception
     */
    private function addHeaders(): array
    {
        return [
            'x-correlation-id' => [
                Log::getSessionId()
            ],
            'Authorization'    => [
                'Bearer ' . $this->generateToken()
            ]
        ];
    }

    /**
     * @return string|null
     *
     * @throws Exception
     */
    private function generateToken():? string
    {
        $azureADToken = new AzureActiveDirectoryAccessToken(
            config('clientapis.configService.aadAuth.clientId'),
            config('clientapis.configService.aadAuth.tenant')
        );

        return $azureADToken->getToken(
            config('clientapis.configService.aadAuth.clientSecret'),
            config('clientapis.configService.aadAuth.resource')
        );
    }
}
