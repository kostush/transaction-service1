<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;

/**
 * Class MappingCriteriaRocketgate
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
class MappingCriteriaRocketgate extends MappingCriteria
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $reasonCode;

    /**
     * @var string
     */
    protected $merchantAccount;

    /**
     * @var string
     */
    protected $bankResponseCode;

    /**
     * MappingCriteriaRocketgate constructor.
     *
     * @param string $billerName       Biller name
     * @param string $merchantId       Merchant id
     * @param string $reasonCode       Reason code sent by the bank
     * @param string $merchantAccount  Merchant account
     * @param string $bankResponseCode Bank response code
     */
    public function __construct(
        string $billerName,
        string $merchantId,
        string $reasonCode,
        string $merchantAccount,
        string $bankResponseCode
    ) {
        $this->billerName       = $billerName;
        $this->merchantId       = $merchantId;
        $this->merchantAccount  = $merchantAccount;
        $this->reasonCode       = $reasonCode;
        $this->bankResponseCode = $bankResponseCode;
    }

    /**
     * @param BillerResponse $billerResponse Biller response
     *
     * @return MappingCriteriaRocketgate
     * @throws Exception
     */
    public static function create(BillerResponse $billerResponse): self
    {
        // There is a case when the biller response is manually created as an aborted transaction
        // when falling back from the circuit breaker.
        // (Infrastructure/Domain/Model/RocketgateBillerResponse.php:createAbortedResponse)
        // When this happens requestPayload and responsePayload are set as null
        // We are also checking the request payload because we are using merchantID from the request
        // because it is not available on the response for now. Once we have it in the response
        // we can remove this condition.
        if (empty($billerResponse->requestPayload()) || empty($billerResponse->responsePayload())) {
            Log::info(
                "ErrorClassification Rocketgate mapping criteria cannot be created." .
                "Biller response is an empty aborted transaction.",
                [
                    'requestPayload'  => $billerResponse->requestPayload(),
                    'responsePayload' => $billerResponse->responsePayload()
                ]
            );

            // We are creating a mapping without values so that the error classifications will return default values
            // to still have it included in the transaction response.
            return new static(
                BillerSettings::ROCKETGATE, '', '', '', ''
            );
        }

        // We need to get the merchant from the request payload until RG will make it available on the response.
        $requestPayload = json_decode($billerResponse->requestPayload(), true);

        $responseDecoded = json_decode($billerResponse->responsePayload(), true);

        return new static(
            BillerSettings::ROCKETGATE,
            (string) ($requestPayload['merchantID'] ?? ''),
            (string) ($responseDecoded['reasonCode'] ?? ''),
            (string) ($responseDecoded['merchantAccount'] ?? ''),
            (string) ($responseDecoded['bankResponseCode'] ?? '')
        );
    }

    /**
     * @return string
     */
    public function merchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function reasonCode(): string
    {
        return $this->reasonCode;
    }

    /**
     * @return string
     */
    public function merchantAccount(): string
    {
        return $this->merchantAccount;
    }

    /**
     * @return string
     */
    public function bankResponseCode(): string
    {
        return $this->bankResponseCode;
    }
}
