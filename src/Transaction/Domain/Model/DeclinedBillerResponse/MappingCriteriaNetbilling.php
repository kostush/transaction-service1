<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;

/**
 * Class MappingCriteriaNetbilling
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
class MappingCriteriaNetbilling extends MappingCriteria
{
    /**
     * @var string
     */
    protected $processor;

    /**
     * @var string
     */
    protected $authMessage;

    /**
     * MappingCriteriaNetbilling constructor.
     *
     * @param string $billerName  Biller name
     * @param string $processor   Processor
     * @param string $authMessage Auth message sent by the bank
     */
    private function __construct(string $billerName, string $processor, string $authMessage)
    {
        $this->billerName  = $billerName;
        $this->processor   = $processor;
        $this->authMessage = $authMessage;
    }

    /**
     * @param BillerResponse $billerResponse Biller response
     *
     * @return MappingCriteriaNetbilling
     * @throws Exception
     */
    public static function create(BillerResponse $billerResponse): self
    {
        // There is a case when the biller response is manually created as an aborted transaction
        // when falling back from the circuit breaker.
        // (Infrastructure/Domain/Model/NetbillingBillerResponse.php:createAbortedResponse)
        // When this happens responsePayload are set as null
        if (empty($billerResponse->responsePayload())) {
            Log::info(
                "ErrorClassification Netbilling mapping criteria cannot be created." .
                "Biller response is an aborted transaction.",
                [
                    'responsePayload' => $billerResponse->responsePayload()
                ]
            );

            // We are creating a mapping without values so that the error classifications will return default values
            // to still have it included in the transaction response.
            return new static(BillerSettings::NETBILLING, '', '');
        }

        $responseDecoded = json_decode($billerResponse->responsePayload(), true);

        return new static(
            BillerSettings::NETBILLING,
            (string) ($responseDecoded['processor'] ?? ''),
            (string) ($responseDecoded['auth_msg'] ?? '')
        );
    }

    /**
     * @return string
     */
    public function processor(): string
    {
        return $this->processor;
    }

    /**
     * @return string
     */
    public function authMessage(): string
    {
        return $this->authMessage;
    }
}
