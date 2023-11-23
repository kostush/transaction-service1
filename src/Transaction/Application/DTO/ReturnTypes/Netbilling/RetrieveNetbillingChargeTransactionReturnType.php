<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Netbilling\Domain\Model\NetbillingErrorCodes;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\ExistingCreditCardTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\NewCreditCardTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveNetbillingChargeTransactionReturnType extends RetrieveTransactionReturnType
{
    const ACCEPTED_PAYMENT_INFORMATION_TYPES = [
        'newCreditCardTransaction'      => CreditCardInformation::class,
        'existingCreditCardTransaction' => NetbillingPaymentTemplateInformation::class
    ];

    /**
     * @var string
     * @deprecated
     */
    private $transactionId;

    /** @var string */
    private $currency;

    /** @var string */
    private $siteId;

    /** @var string */
    private $paymentType;

    /** @var MemberReturnType */
    private $member;

    /** @var int */
    private $cardExpirationYear;

    /** @var int */
    private $cardExpirationMonth;

    /** @var string $cardDescription */
    private $cardDescription;

    /** @var TransactionReturnType */
    protected $transaction;

    /**
     * @var array|null
     */
    private $billerSettings;

    /**
     * @var string|null
     */
    private $billerMemberId;

    /**
     * @var array
     */
    private $billerTransactions;

    /**
     * TransactionPayload constructor.
     *
     * @param string                $billerId            BillerId
     * @param string                $billerName          BillerName
     * @param string                $merchantId          MerchantId
     * @param string                $merchantPassword    MerchantPassword
     * @param null|string           $invoiceId           InvoiceId
     * @param null|string           $customerId          CustomerId
     * @param string                $cardHash            CardHash
     * @param string                $transactionId       TransactionId
     * @param string                $billerMemberId      Biller Member Id
     * @param string                $currency            Currency.
     * @param string                $siteId              Site Id.
     * @param string                $paymentType         Payment Type.
     * @param string                $merchantAccount     Merchant Account.
     * @param MemberReturnType|null $member              Member.
     * @param TransactionReturnType $transaction         Transaction.
     * @param int|null              $cardExpirationYear  Card Expiration Year
     * @param int|null              $cardExpirationMonth Card Expiration Month
     * @param string|null           $cardDescription     Card Description
     * @param BillerSettings|null   $billerSettings      Biller settings
     * @param array                 $billerTransactions  Biller Transactions
     */
    protected function __construct(
        string $billerId,
        string $billerName,
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $invoiceId,
        ?string $customerId,
        ?string $cardHash,
        ?string $transactionId,
        ?string $billerMemberId,
        string $currency,
        string $siteId,
        string $paymentType,
        ?string $merchantAccount,
        ?MemberReturnType $member,
        TransactionReturnType $transaction,
        ?int $cardExpirationYear,
        ?int $cardExpirationMonth,
        ?string $cardDescription,
        ?BillerSettings $billerSettings,
        array $billerTransactions

    ) {
        $this->billerId            = $billerId;
        $this->billerName          = $billerName;
        $this->merchantId          = $merchantId;
        $this->merchantPassword    = $merchantPassword;
        $this->invoiceId           = $invoiceId;
        $this->customerId          = $customerId;
        $this->cardHash            = $cardHash;
        $this->transactionId       = $transactionId;
        $this->billerTransactionId = $transactionId;
        $this->billerMemberId      = $billerMemberId;
        $this->currency            = $currency;
        $this->siteId              = $siteId;
        $this->paymentType         = $paymentType;
        $this->merchantAccount     = $merchantAccount;
        $this->member              = $member;
        $this->transaction         = $transaction;
        $this->cardExpirationYear  = $cardExpirationYear;
        $this->cardExpirationMonth = $cardExpirationMonth;
        $this->cardDescription     = $cardDescription;
        $this->billerSettings      = $billerSettings;
        $this->billerTransactions  = $billerTransactions;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RetrieveNetbillingChargeTransactionReturnType
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    public static function createFromEntity(Transaction $transaction): RetrieveNetbillingChargeTransactionReturnType
    {
        $className = get_class($transaction->paymentInformation());

        if (!in_array($className, array_values(self::ACCEPTED_PAYMENT_INFORMATION_TYPES))) {
            throw new InvalidPaymentInformationException();
        }

        $memberPayload      = null;
        $expirationYear     = null;
        $expirationMonth    = null;
        $transactionPayload = null;
        $last4              = null;
        $cardHash           = null;

        if (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['newCreditCardTransaction'] == $className) {
            $memberPayload      = MemberReturnType::createFromCreditCardInfo($transaction->paymentInformation());
            $transactionPayload = NewCreditCardTransactionReturnType::createFromTransaction($transaction);
            $expirationYear     = $transaction->paymentInformation()->expirationYear();
            $expirationMonth    = $transaction->paymentInformation()->expirationMonth();
            $last4              = $transactionPayload->last4();
        } elseif (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['existingCreditCardTransaction'] == $className) {
            $transactionPayload = ExistingCreditCardTransactionReturnType::createFromTransaction($transaction);
            $cardHash           = base64_encode($transaction->paymentInformation()->netBillingCardHash()->value());
        }

        [$billerTransactions, $subsequentOperationFields, $billerInteractionResponses]
            = self::getBillerInteractionsData($transaction);

        $billerInteractionFields = static::getNetbillingBillerInteractionFields(
            $transaction->billerInteractions(),
            $last4
        );

        $billerTransactionId = $billerInteractionFields['billerTransactionId'];

        if (empty($billerTransactionId) && !is_null($transaction->subsequentOperationFieldsToArray())) {
            $billerTransactionId = (string) $transaction->subsequentOperationFieldsToArray()['netbilling']['transId'];
        }

        if (!empty($billerInteractionResponses) && $transaction->status() instanceof Declined) {
            $billerInteractionResponse = json_decode(reset($billerInteractionResponses)->payload(), true);

            $reason = self::getReasonFromBillerInteraction($billerInteractionResponse);
            $code   = NetbillingErrorCodes::mapToInternalCode($reason);

            $transactionPayload->setCode((int) $code);
            $transactionPayload->setReason($reason);

            if (count($billerInteractionResponses) > 1) {
                // keep the auth sale on the biller transactions
                $billerInteractionCollection = new BillerInteractionCollection();
                $billerInteractionCollection->add(end($billerInteractionResponses));

                $billerInteractionFields = static::getNetbillingBillerInteractionFields(
                    $billerInteractionCollection,
                    $last4
                );
            }
        }

        if (empty($cardHash)) {
            $cardHash = $billerInteractionFields['cardHash'];
        }

        $billerSettingsPayload = $transaction->billerChargeSettings();
        $billerMemberId        = $billerInteractionFields['netbillingMemberId'];
        if (empty($billerMemberId) && $billerSettingsPayload != null) {
            $billerMemberId = $billerSettingsPayload->billerMemberId();
        }

        if (empty($billerMemberId) && !is_null($transaction->subsequentOperationFieldsToArray())) {
            if (isset($transaction->subsequentOperationFieldsToArray()['netbilling']['billerMemberId'])) {
                $billerMemberId = (string) $transaction->subsequentOperationFieldsToArray()['netbilling']['billerMemberId'];
            }
        }

        /** @var Transaction $transaction */
        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            "",
            "",
            "",
            "",
            $cardHash,
            $billerTransactionId,
            $billerMemberId,
            $transaction->chargeInformation()->currency()->code(),
            (string) $transaction->siteId(),
            $transaction->paymentType(),
            "",
            $memberPayload,
            $transactionPayload,
            $expirationYear,
            $expirationMonth,
            $billerInteractionFields['cardDescription'],
            $billerSettingsPayload,
            $billerTransactions
        );
    }

    /**
     * @param BillerInteractionCollection $billerInteractions biller response
     * @param string                      $last4              last 4 of card
     * @return array
     */
    protected static function getNetbillingBillerInteractionFields($billerInteractions, $last4): array
    {
        $cardHash            = null;
        $billerTransactionId = null;
        $cardDescription     = null;
        $netbillingMemberId  = "";

        if ($billerInteractions->count()) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($billerInteractions as $billerInteraction) {
                if ($billerInteraction->type() == BillerInteraction::TYPE_RESPONSE) {
                    $payload = json_decode($billerInteraction->payload());

                    if (isset($payload->trans_id)) {
                        $billerTransactionId = $payload->trans_id;

                        $cardHash = base64_encode("CS:" . $billerTransactionId . ":" . $last4);
                    }

                    if (isset($payload->member_id)) {
                        $netbillingMemberId = $payload->member_id;
                    }

                    break;
                }
            }
        }
        return [
            'cardHash'            => $cardHash,
            'billerTransactionId' => $billerTransactionId,
            'cardDescription'     => $cardDescription,
            'netbillingMemberId'  => $netbillingMemberId
        ];
    }

    /**
     * @param BillerInteraction $billerInteraction The biller Interaction
     * @return NetbillingBillerTransaction
     */
    private static function buildBillerTransaction(
        BillerInteraction $billerInteraction
    ): NetbillingBillerTransaction {

        $payload = json_decode($billerInteraction->payload());

        return new NetbillingBillerTransaction(
            $payload->member_id ?? null,
            $payload->trans_id ?? null,
            self::buildBillerTransactionType(
                isset($payload->settle_amount) ? self::isFreeSale($payload->settle_amount) : false
            )
        );
    }

    /**
     * @param bool $isFreeSale Free sale flag
     * @return string
     */
    private static function buildBillerTransactionType(bool $isFreeSale): string
    {
        return $isFreeSale ? NetbillingBillerTransaction::AUTH_TYPE : NetbillingBillerTransaction::SALE_TYPE;
    }

    /**
     * @param string $approvedAmount Approved amount.
     * @return bool
     */
    private static function isFreeSale(string $approvedAmount): bool
    {
        return NetbillingBillerTransaction::FREE_SALE_APPROVED_AMOUNT == $approvedAmount;
    }

    /**
     * @param array $responseInteractions The biller interaction object
     * @return array
     */
    public static function buildResponseData(array $responseInteractions): array
    {
        if (count($responseInteractions) == 0) {
            return [];
        }
        //add the first interaction
        $billerTransactions = [self::buildBillerTransaction(reset($responseInteractions))];

        //add the second interaction if it exists
        if (count($responseInteractions) > 1) {
            $billerTransactions[] = self::buildBillerTransaction(end($responseInteractions));
        }

        return $billerTransactions;
    }

    /**
     * @param array $billerInteractionResponse Biller interaction response.
     * @return string
     */
    private static function getReasonFromBillerInteraction(array $billerInteractionResponse): string
    {
        if (isset($billerInteractionResponse['auth_msg'])) {
            return $billerInteractionResponse['auth_msg'];
        }

        if (isset($billerInteractionResponse['message'])) {
            return $billerInteractionResponse['message'];
        }

        return "";
    }

    /** TODO done to align with the new TS */
    /**
     * @param Transaction $transaction Transaction.
     * @return array
     */
    public static function getBillerInteractionsData(Transaction $transaction): array
    {
        $billerInteractions         = $transaction->billerInteractions()->toArray();
        $billerTransactions         = [];
        $billerInteractionResponses = [];
        $subsequentOperationFields  = null;

        if (count($billerInteractions) > 0) {
            self::sortBillerInteractions($billerInteractions);
            $billerInteractionResponses = self::getResponseInteractions($billerInteractions);
            $billerInteractionRequests  = self::getRequestInteractions($billerInteractions);
            $billerMemberId             = self::getBillerMemberIdFromBillerInteraction($billerInteractionRequests);
            $billerTransactions         = self::buildResponseData($billerInteractionResponses);

            $subsequentOperationFields  = self::getSubsequentOperationFieldsFromResponse(
                $billerInteractionResponses,
                $billerMemberId
            );
        }

        // fallback to subsequent operation fields if biller interaction is empty
        if (empty($billerTransactions) && !is_null($transaction->subsequentOperationFieldsToArray())) {
            $billerTransactions = [self::buildBillerTransactionFromSubsequentOp($transaction->subsequentOperationFieldsToArray(), $transaction->isFreeSale())];
        }

        return [$billerTransactions, $subsequentOperationFields, $billerInteractionResponses];
    }

    /** TODO done to align with the new TS */
    /**
     * @param array $billerTransactionsData Biller transaction data.
     * @return null|string
     */
    public static function getEncodedBillerTransactions(array $billerTransactionsData): ?string
    {
        $billerTransactions = [];

        foreach ($billerTransactionsData as $billerTransaction) {
            $billerTransactions[] = $billerTransaction->toArray();
        }

        return json_encode($billerTransactions);
    }

    /** TODO done to align with the new TS */
    /**
     * @param array       $responseInteractions Response Interactions
     * @param string|null $billerMemberId       Biller member id.
     * @return string|null
     */
    public static function getSubsequentOperationFieldsFromResponse(
        array $responseInteractions,
        ?string $billerMemberId = null
    ): ?string {
        $billerInteraction         = end($responseInteractions);
        $subsequentOperationFields = [];

        if ($billerInteraction instanceof BillerInteraction) {
            $payload = json_decode($billerInteraction->payload(), true);


            if (!empty($payload['trans_id'])) {
                $subsequentOperationFields['transId'] = $payload['trans_id'];
            }

            if (!empty($payload['member_id']) || !empty($billerMemberId)) {
                $subsequentOperationFields['billerMemberId']
                    = !empty($payload['member_id']) ? $payload['member_id'] : $billerMemberId;
            }

            if (!empty($payload['recurring_id'])) {
                $subsequentOperationFields['recurringId'] = $payload['recurring_id'];
            }
        }

        return json_encode(
            [
                'netbilling' => $subsequentOperationFields
            ]
        );
    }

    /**
     * @param array $requestInteractions Request Interactions
     * @return string
     */
    public static function getBillerMemberIdFromBillerInteraction(array $requestInteractions): string
    {
        $billerInteraction = end($requestInteractions);
        $billerMemberId    = '';

        if ($billerInteraction instanceof BillerInteraction) {
            $payload = json_decode($billerInteraction->payload(), true);

            if (array_key_exists('memberId', $payload)) {
                $billerMemberId = $payload['memberId'];
            }
        }

        return $billerMemberId;
    }

    /**
     * @param array $subsequentOperationFields
     * @param bool  $isFreeSale
     * @return \ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\NetbillingBillerTransaction
     */
    private static function buildBillerTransactionFromSubsequentOp(array $subsequentOperationFields, bool $isFreeSale): NetbillingBillerTransaction
    {
        return new NetbillingBillerTransaction(
            (string) $subsequentOperationFields['netbilling']['billerMemberId'] ?? null,
            (string) $subsequentOperationFields['netbilling']['transId'] ?? null,
            self::buildBillerTransactionType($isFreeSale)
        );
    }
}
