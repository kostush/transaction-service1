<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ErrorException;
use Probiller\Common\BillerUnifiedGroupErrorResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraData;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraDataNetbilling;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraDataRocketgate;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteria;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingUnifiedGroupError;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateUnifiedGroupError;

class ConfigTranslatingService implements DeclinedBillerResponseExtraDataRepository
{
    /**
     * @var ConfigServiceClient
     */
    protected $configServiceClient;

    /**
     * @param ConfigServiceClient $configServiceClient
     */
    public function __construct(ConfigServiceClient $configServiceClient)
    {
        $this->configServiceClient = $configServiceClient;
    }

    /**
     * @param MappingCriteria $mappingCriteria
     * @return DeclinedBillerResponseExtraData|null
     *
     * @throws \ProBillerNG\Logger\Exception
     */
    public function retrieve(MappingCriteria $mappingCriteria): ?DeclinedBillerResponseExtraData
    {
        $response = $this->configServiceClient->retrieveBillerUnifiedGroupError($mappingCriteria);

        return $this->translateUnifiedGroupErrorResponse($response, $mappingCriteria->billerName());
    }

    /**
     * @param BillerUnifiedGroupErrorResponse|null $response
     * @param string                               $billerName
     *
     * @return DeclinedBillerResponseExtraData|null
     *
     * @throws ErrorException
     */
    private function translateUnifiedGroupErrorResponse(?BillerUnifiedGroupErrorResponse $response, string $billerName): ?DeclinedBillerResponseExtraData
    {
        $extraData = null;
        if (!is_null($response)) {
            $mappingCriteria = $response->getMappingCriteria();

            if (!is_null($mappingCriteria->count())) {
                switch ($billerName) {
                    case BillerSettings::ROCKETGATE:
                        $extraData = new DeclinedBillerResponseExtraDataRocketgate();
                        $extraData->setReasonCode((string) $mappingCriteria->offsetGet(RocketgateUnifiedGroupError::REASON_CODE));
                        $extraData->setBankResponseCode((string) $mappingCriteria->offsetGet(RocketgateUnifiedGroupError::BANK_RESPONSE_CODE));

                        break;
                    case BillerSettings::NETBILLING:
                        $extraData = new DeclinedBillerResponseExtraDataNetbilling();
                        $extraData->setProcessor((string) $mappingCriteria->offsetGet(NetbillingUnifiedGroupError::PROCESSOR));
                        $extraData->setAuthMessage((string) $mappingCriteria->offsetGet(NetbillingUnifiedGroupError::AUTH_MESSAGE));

                        break;
                }

                $unifiedErrorData = $response->getData();
                if (!is_null($extraData) && !is_null($unifiedErrorData)) {
                    $extraData->setGroupDecline($unifiedErrorData->getGroupDecline());
                    $extraData->setErrorType($unifiedErrorData->getErrorType());
                    $extraData->setGroupMessage($unifiedErrorData->getGroupMessage());
                    $extraData->setRecommendedAction($unifiedErrorData->getRecommendedAction());
                }
            }
        }

        return $extraData;
    }
}
