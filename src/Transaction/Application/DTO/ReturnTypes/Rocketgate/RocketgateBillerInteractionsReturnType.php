<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionRequestResponseInteractionTrait;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;

class RocketgateBillerInteractionsReturnType
{
    use TransactionRequestResponseInteractionTrait;

    /**
     * @var null|string
     */
    private $cardHash;

    /**
     * @var null|string
     */
    private $cardDescription;

    /**
     * @var RocketgateBillerTransactionCollection
     */
    private $billerTransactions;

    /**
     * @var  null|string
     */
    private $subsequentOperationFields;

    /**
     * @var bool
     */
    private $threeDSecured;

    /**
     * RocketgateBillerTransactionsReturnType constructor.
     * @param RocketgateBillerTransactionCollection $billerTransactionCollection The biller transactions collection.
     * @param string|null                           $cardHash                    The card hash.
     * @param string|null                           $cardDescription             The card description.
     * @param string|null                           $subsequentOperationFields   Subsequent Operation Fields.
     * @param bool                                  $threeDSecured               The 3DS flag.
     */
    private function __construct(
        RocketgateBillerTransactionCollection $billerTransactionCollection,
        ?string $cardHash,
        ?string $cardDescription,
        ?string $subsequentOperationFields,
        bool $threeDSecured
    ) {
        $this->billerTransactions        = $billerTransactionCollection;
        $this->subsequentOperationFields = $subsequentOperationFields;
        $this->cardHash                  = $cardHash;
        $this->cardDescription           = $cardDescription;
        $this->threeDSecured             = $threeDSecured;
    }

    /**
     * @param mixed $billerInteractions The biller interactions object
     * @param bool  $threeDSecured      ThreeDS usage flag
     * @return self
     */
    public static function createFromBillerInteractionsCollection($billerInteractions, bool $threeDSecured): self
    {
        $cardHash                  = null;
        $billerTransactionId       = null;
        $cardDescription           = null;
        $subsequentOperationFields = null;

        $billerTransactionCollection = new RocketgateBillerTransactionCollection();
        if (!$billerInteractions->count()) {
            return new static(
                $billerTransactionCollection,
                $cardHash,
                $cardDescription,
                $subsequentOperationFields,
                $threeDSecured
            );
        }

        $interactions = $billerInteractions->toArray();

        //We are sorting here request and after response based on time (order is old to new)
        self::sortBillerInteractions($interactions);

        list($cardHash, $cardDescription, $subsequentOperationFields) = self::buildResponseData(
            $billerTransactionCollection,
            self::getResponseInteractions($interactions),
            self::getRequestInteractions($interactions)
        );

        return new static(
            $billerTransactionCollection,
            $cardHash,
            $cardDescription,
            $subsequentOperationFields,
            $threeDSecured
        );
    }

    /**
     * @param RocketgateBillerTransactionCollection $billerTransactionCollection The biller transaction collection
     * @param array                                 $responseInteractions        The biller interaction object
     * @param array                                 $requestInteractions         The biller requests array
     * @return array
     */
    public static function buildResponseData(
        RocketgateBillerTransactionCollection $billerTransactionCollection,
        array $responseInteractions,
        array $requestInteractions
    ): array {

        // in case the request to Rocketgate is without 3ds and the biller response contains reason code 228
        // then this interaction will be removed to remain first the 3ds biller transaction
        if (count($responseInteractions) > 1 && self::threeDSRequired(reset($responseInteractions))) {
            array_shift($responseInteractions);
            array_shift($requestInteractions);
        }

        $isThreeDsTwoInitializedWithNFS = self::threeDSTwoWithNFS($responseInteractions);
        $isThreeDsTwoInitialized        = self::threeDSTwoInitiation(reset($responseInteractions));
        $isThreeDsSimplifiedInitialized = self::threeDSSimplifiedInitiation(reset($responseInteractions));
        $pendingTransaction             = $isThreeDsTwoInitialized && count($responseInteractions) == 1;

        if (count($responseInteractions) > 1 && $isThreeDsTwoInitialized) {
            array_shift($responseInteractions);
            array_shift($requestInteractions);
        }

        //add the first interaction
        $billerTransactionCollection->add(
            self::buildRocketgateBillerTransaction(
                reset($requestInteractions),
                reset($responseInteractions),
                $pendingTransaction
                || self::isFailedThreeD(reset($responseInteractions))
                || self::authRequiredForThreeD(reset($responseInteractions))
                || $isThreeDsSimplifiedInitialized
            )
        );

        //add the second interaction if it exists
        if (count($responseInteractions) > 1) {
            if ($isThreeDsTwoInitializedWithNFS) {
                array_shift($responseInteractions);
                array_shift($requestInteractions);
                for ($i=0; $i<count($responseInteractions); $i++) {
                    $billerTransactionCollection->add(
                        self::buildRocketgateBillerTransaction(
                            $requestInteractions[$i],
                            $responseInteractions[$i]
                        )
                    );
                }
            } else {
                $billerTransactionCollection->add(
                    self::buildRocketgateBillerTransaction(
                        end($requestInteractions),
                        end($responseInteractions)
                    )
                );
            }
        }

        $cardHash                  = self::getCardHashFromResponse(end($responseInteractions));
        $cardDescription           = self::getCardDescriptionFromResponse(end($responseInteractions));
        $subsequentOperationFields = self::getSubsequentOperationFieldsFromResponse(end($responseInteractions));

        return [$cardHash, $cardDescription, $subsequentOperationFields];
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction
     * @return bool
     */
    public static function isFailedThreeD(BillerInteraction $billerInteraction): bool
    {
        $payload = json_decode($billerInteraction->payload(), true, 512, JSON_THROW_ON_ERROR);

        if (!empty($payload['PAYMENT_LINK_URL'])) {
            return false;
        }

        return RocketgateErrorCodes::isFailed3dsResponse((int) $payload['reasonCode']);
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction
     * @return bool
     */
    public static function authRequiredForThreeD(BillerInteraction $billerInteraction): bool
    {
        $payload = json_decode($billerInteraction->payload(), true, 512, JSON_THROW_ON_ERROR);

        if (!empty($payload['PAYMENT_LINK_URL'])) {
            return false;
        }

        return $payload['reasonCode'] == RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED;
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction
     * @return bool
     */
    public static function threeDSTwoInitiation(BillerInteraction $billerInteraction): bool
    {
        $payload = json_decode($billerInteraction->payload());

        if (property_exists($payload, 'reasonCode')) {
            return $payload->reasonCode == RocketgateErrorCodes::RG_CODE_3DS2_INITIATION;
        }

        return false;
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction
     * @return bool
     */
    public static function threeDSRequired(BillerInteraction $billerInteraction): bool
    {
        $payload = json_decode($billerInteraction->payload());

        if (property_exists($payload, 'reasonCode')) {
            return $payload->reasonCode == RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED;
        }

        return false;
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction.
     * @return bool
     */
    public static function threeDSSimplifiedInitiation(BillerInteraction $billerInteraction): bool
    {
        $payload = json_decode($billerInteraction->payload());

        if (property_exists($payload, RocketgateBillerResponse::PAYMENT_LINK_URL)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $billerInteractions Biller interactions.
     * @return bool
     */
    public static function threeDSTwoWithNFS(array $billerInteractions): bool
    {
        $threeDsTwo        = false;
        $threeDsTwoWithNFS = false;
        foreach ($billerInteractions as $billerInteraction) {
            $payload = json_decode($billerInteraction->payload());

            if (property_exists($payload, 'reasonCode')) {
                if ($payload->reasonCode == RocketgateErrorCodes::RG_CODE_3DS2_INITIATION) {
                    $threeDsTwo = true;
                }

                if ($threeDsTwo && $payload->reasonCode == RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT) {
                    $threeDsTwoWithNFS = true;
                }
            }
        }

        return $threeDsTwoWithNFS;
    }


    /**
     * @param BillerInteraction $response The biller interaction
     * @return string|null
     */
    private static function getCardHashFromResponse(BillerInteraction $response): ?string
    {
        $payload = json_decode($response->payload());

        return $payload->cardHash ?? null;
    }

    /**
     * @param BillerInteraction $response The biller interaction
     * @return string|null
     */
    private static function getCardDescriptionFromResponse(BillerInteraction $response): ?string
    {
        $payload = json_decode($response->payload());

        return $payload->cardDescription ?? null;
    }

    /** TODO done to align with the new TS */
    /**
     * @param BillerInteraction $response The biller interaction
     * @return string|null
     */
    private static function getSubsequentOperationFieldsFromResponse(BillerInteraction $response): ?string
    {
        $payload = json_decode($response->payload());

        return json_encode(
            [
                'rocketgate' => [
                    'referenceGuid'      => $payload->guidNo ?? '',
                    'merchantAccount'    => $payload->merchantAccount ?? '',
                    'merchantInvoiceId'  => $payload->merchantInvoiceID ?? '',
                    'merchantCustomerId' => $payload->merchantCustomerID ?? '',
                ]
            ]
        );
    }

    /**
     * @param BillerInteraction $billerInteractionRequest  The biller Interaction request
     * @param BillerInteraction $billerInteractionResponse The biller Interaction reponse
     * @param bool              $isThreeDSInitialRequest   3ds request flag
     * @return RocketgateBillerTransaction
     */
    public static function buildRocketgateBillerTransaction(
        BillerInteraction $billerInteractionRequest,
        BillerInteraction $billerInteractionResponse,
        bool $isThreeDSInitialRequest = false
    ): RocketgateBillerTransaction {
        $responsePayload = json_decode($billerInteractionResponse->payload());
        $requestPayload  = json_decode($billerInteractionRequest->payload());

        $requestPayload = (!empty($requestPayload)) ? $requestPayload : null;

        $approvedAmount = $responsePayload->approvedAmount ?? null;

        return new RocketgateBillerTransaction(
            $responsePayload->merchantInvoiceID ?? $responsePayload->invoiceId ?? $requestPayload->merchantInvoiceID ?? $requestPayload->merchant_invoice_id ?? null,
            $responsePayload->merchantCustomerID ?? $responsePayload->customerId ?? $requestPayload->merchantCustomerID ?? $requestPayload->merchant_customer_id ?? null,
            $responsePayload->guidNo ?? $responsePayload->transId ?? null,
            self::buildRocketgateBillerTransactionType(
                self::isFreeSale($approvedAmount),
                $isThreeDSInitialRequest,
                !$isThreeDSInitialRequest && self::isCardUploadBillerTransaction($requestPayload, $responsePayload)
            )
        );
    }


    /**
     * @param bool $isFreeSale              Free sale flag
     * @param bool $isThreeDSInitialRequest 3DS usage flag
     * @param bool $isCardUpload            CardUpload flag
     * @return string
     */
    private static function buildRocketgateBillerTransactionType(
        bool $isFreeSale,
        bool $isThreeDSInitialRequest,
        bool $isCardUpload
    ): string {

        if ($isCardUpload) {
            return RocketgateBillerTransaction::CARD_UPLOAD_TYPE;
        }

        if ($isThreeDSInitialRequest) {
            return RocketgateBillerTransaction::THREE_D_SECURED_TYPE;
        }

        if (!$isThreeDSInitialRequest && !$isFreeSale) {
            return RocketgateBillerTransaction::SALE_TYPE;
        }

        if (!$isThreeDSInitialRequest && $isFreeSale) {
            return RocketgateBillerTransaction::AUTH_TYPE;
        }
    }

    /**
     * @param \stdClass $requestPayload  Payload
     * @param \stdClass $responsePayload Payload
     * @return bool
     */
    private static function isCardUploadBillerTransaction(?\stdClass $requestPayload, \stdClass $responsePayload): bool
    {
        //for transactions migrated through user sync we need this extra check
        if (isset($requestPayload->transactionType)
            && strcasecmp($requestPayload->transactionType, RocketgateBillerTransaction::CARD_UPLOAD_TYPE) == 0) {
            return true;
        }

        return false;
    }


    /**
     * @param null|string $approvedAmount Approved amount
     * @return bool
     */
    private static function isFreeSale(?string $approvedAmount): bool
    {
        return RocketgateBillerTransaction::FREE_SALE_APPROVED_AMOUNT === $approvedAmount;
    }

    /**
     * @param array $billerInteractions The biller interaction array
     * @return bool
     */
    public static function isThreeDSecuredInitialRequest(array $billerInteractions): bool
    {
        /** @var BillerInteraction $firstBillerInteraction */
        $firstBillerInteraction = reset($billerInteractions);

        if (!$firstBillerInteraction instanceof BillerInteraction) {
            return false;
        }

        $payload = json_decode($firstBillerInteraction->payload(), true);
        if (isset($payload['use3DSecure']) && $payload['use3DSecure'] == "TRUE") {
            return true;
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function cardHash(): ?string
    {
        return $this->cardHash;
    }

    /**
     * @return string|null
     */
    public function cardDescription(): ?string
    {
        return $this->cardDescription;
    }

    /**
     * @return RocketgateBillerTransactionCollection
     */
    public function billerTransactions(): RocketgateBillerTransactionCollection
    {
        return $this->billerTransactions;
    }

    /**
     * @return bool
     */
    public function threeDSecured(): bool
    {
        return $this->threeDSecured;
    }

    /** TODO done to align with the new TS */
    /**
     * @return null|string
     */
    public function getEncodedSubsequentOperationFields(): ?string
    {
        return $this->subsequentOperationFields;
    }

    /** TODO done to align with the new TS */
    /**
     * @return null|string
     */
    public function getEncodedBillerTransactions(): ?string
    {
        $billerTransactions = [];

        foreach ($this->billerTransactions()->toArray() as $billerTransaction) {
            $billerTransactions[] = $billerTransaction->toArray();
        }

        return json_encode($billerTransactions);
    }
}
