<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use DateTime;
use DateTimeImmutable;
use Google\Cloud\Core\Timestamp;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment as CommandPayment;
use ProBillerNG\Transaction\Domain\AggregateRoot;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteria;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use ProBillerNG\Transaction\Domain\Model\Event\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Logger\Exception as LoggerException;

abstract class Transaction extends AggregateRoot
{
    public const THREE_DS_ONE = 1;

    public const THREE_DS_TWO = 2;

    /**
     * @var Transaction
     */
    public $previousTransaction;

    /**
     * @var array
     * //TODO could not add this to the event class and exclude it from serialization. Adding here temporarily
     */
    protected $transactionTypeMap = [
        RebillUpdateTransaction::class => BaseEvent::REBILL_UPDATE_TRANSACTION,
        ChargeTransaction::class       => BaseEvent::CHARGE_TRANSACTION
    ];

    /**
     * @var TransactionId
     */
    protected $transactionId;

    /** TODO kept for backwards compatibility - billerId removal */
    /**
     * @var string
     */
    protected $billerId;

    /**
     * @var string
     */
    public $billerName;

    /**
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var DateTimeImmutable
     */
    protected $updatedAt;

    /**
     * @var AbstractStatus
     */
    protected $status;

    /**
     * @var SiteId
     */
    protected $siteId;

    /**
     * @var string|null
     */
    protected $siteName;

    /**
     * @var PaymentInformation
     */
    protected $paymentInformation;

    /**
     * @var ChargeInformation
     */
    protected $chargeInformation;

    /**
     * @var BillerSettings
     */
    protected $billerChargeSettings;

    /**
     * @var BillerInteractionCollection|null
     */
    protected $billerInteractions;

    /**
     * @var string
     */
    protected $paymentType;

    /**
     * @var string|null
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var int|null
     */
    protected $threedsVersion;

    /**
     * @var bool
     */
    protected $shouldReturn400 = false;

    /**
     * @var bool
     */
    protected $requiredToUse3D = false;

    /**
     * @var bool
     */
    protected $with3D = false;

    /**
     * @var null|string
     */
    protected $returnUrl = null;

    /**
     * @var bool
     */
    public $isNsf = false;

    /**
     * @var BillerMember|null
     */
    protected $billerMember;

    /**
     * @var string|null
     */
    protected $legacyTransactionId;

    /**
     * @var string|null
     */
    protected $legacyMemberId;

    /**
     * @var string|null
     */
    protected $legacySubscriptionId;

    /** TODO done to align with the new TS */

    /**
     * @var string|null
     */
    public $originalTransactionId = null;

    /**
     * @var string|null
     */
    public $billerTransactions;

    /**
     * @var string|null
     */
    public $subsequentOperationFields;

    /**
     * @var string|null
     */
    public $subsequentOperationFieldsLegacy;

    /**
     * @var bool
     */
    public $isPrimaryCharge = true;

    /**
     * @var string
     */
    public $chargeId = '00000000-0000-0000-0000-000000000000';

    /**
     * @var int
     */
    public $version = 1;

    /**
     * @var null | MappingCriteriaRocketgate
     */
    public $errorMappingCriteria = null;


    /**
     * @param string|null                   $siteId                   The site id
     * @param string|null                   $billerName               The biller name
     * @param CommandPayment                $payment                  The payment object
     * @param CreditCardOwner|null          $creditCardOwner          The credit card owner object
     * @param CreditCardBillingAddress|null $creditCardBillingAddress The credit card billing address object
     * @param ChargeInformation             $chargeInformation        The charge information
     * @param BillerSettings                $billerFields             The biller fields
     * @param Transaction|null              $previousTransaction      The previous transaction
     * @param bool                          $useThreeD                Use threed
     * @param null|string                   $returnUrl                The 3ds return url
     * @param bool                          $shouldCheckDate          Should check if date is valid
     *
     * @return ChargeTransaction
     * @throws Exception\InvalidCreditCardCvvException
     * @throws Exception\InvalidCreditCardExpirationDateException
     * @throws Exception\InvalidCreditCardNumberException
     * @throws Exception\InvalidCreditCardTypeException
     * @throws Exception\InvalidMerchantInformationException
     * @throws Exception\MissingMerchantInformationException
     * @throws InvalidChargeInformationException
     * @throws LoggerException
     */
    public static function createRocketgateTransactionWithNewCreditCardInformation(
        ?string $siteId,
        ?string $billerName,
        CommandPayment $payment,
        ?CreditCardOwner $creditCardOwner,
        ?CreditCardBillingAddress $creditCardBillingAddress,
        ChargeInformation $chargeInformation,
        BillerSettings $billerFields,
        ?Transaction $previousTransaction,
        bool $useThreeD,
        ?string $returnUrl = null,
        bool $shouldCheckDate = true
    ): self {
        return new static(
            TransactionId::create(),
            $billerName,
            RocketGateChargeSettings::create(
                $billerFields->merchantId(),
                $billerFields->merchantPassword(),
                $billerFields->merchantCustomerId(),
                $billerFields->merchantInvoiceId(),
                $billerFields->merchantAccount(),
                $billerFields->merchantSiteId(),
                $billerFields->merchantProductId(),
                $billerFields->merchantDescriptor(),
                $billerFields->ipAddress(),
                null,
                $billerFields->sharedSecret(),
                $billerFields->simplified3DS()
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            CreditCardInformation::create(
                true, //should this be part of the payload?
                CreditCardNumber::create((string) $payment->information()->number()),
                $creditCardOwner,
                $creditCardBillingAddress,
                $payment->information()->cvv(),
                $payment->information()->expirationMonth(),
                $payment->information()->expirationYear(),
                $shouldCheckDate
            ),
            SiteId::createFromString($siteId),
            $chargeInformation,
            $payment->type(),
            new BillerInteractionCollection(),
            $useThreeD,
            $returnUrl
        );
    }

    /**
     * @param string|null       $siteId              The site id
     * @param string|null       $billerName          The biller name
     * @param CommandPayment    $payment             The payment object
     * @param ChargeInformation $chargeInformation   The charge information
     * @param BillerSettings    $billerFields        The biller fields
     * @param Transaction|null  $previousTransaction The previous transaction
     * @param bool              $requiredToUse3D     Three D is triggered of not
     * @param string|null       $returnUrl           The 3ds return url
     *
     * @return ChargeTransaction
     * @throws Exception\InvalidChargeInformationException
     * @throws Exception\InvalidMerchantInformationException
     * @throws Exception\MissingMerchantInformationException
     * @throws LoggerException
     */
    public static function createRocketgateTransactionWithExistingCreditCardInformation(
        ?string $siteId,
        ?string $billerName,
        CommandPayment $payment,
        ChargeInformation $chargeInformation,
        BillerSettings $billerFields,
        ?Transaction $previousTransaction,
        bool $requiredToUse3D = false,
        ?string $returnUrl = null
    ): self {
        $merchantCustomerId = MerchantCustomerId::create($billerFields->merchantCustomerId());

        return new static(
            TransactionId::create(),
            $billerName,
            RocketGateChargeSettings::create(
                $billerFields->merchantId(),
                $billerFields->merchantPassword(),
                $merchantCustomerId->value(),
                $billerFields->merchantInvoiceId(),
                $billerFields->merchantAccount(),
                $billerFields->merchantSiteId(),
                $billerFields->merchantProductId(),
                $billerFields->merchantDescriptor(),
                $billerFields->ipAddress(),
                $billerFields->referringMerchantId(),
                $billerFields->sharedSecret(),
                $billerFields->simplified3DS()
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            PaymentTemplateInformation::create(
                RocketGateCardHash::create($payment->information->cardHash()),
                $merchantCustomerId
            ),
            SiteId::createFromString($siteId),
            $chargeInformation,
            $payment->type(),
            new BillerInteractionCollection(),
            $requiredToUse3D,
            $returnUrl
        );
    }

    /**
     * @param string|null         $siteId              The site id
     * @param string|null         $billerName          The biller name
     * @param CommandPayment      $payment             The payment object
     * @param OwnerInfo|null      $ownerInfo           The owner info
     * @param BillingAddress|null $billingAddress      The billing address
     * @param ChargeInformation   $chargeInformation   The charge information
     * @param BillerSettings      $billerFields        The biller fields
     * @param Transaction|null    $previousTransaction The previous transaction
     * @param bool                $useThreeD           Use threed
     *
     * @return ChargeTransaction
     * @throws LoggerException
     * @throws Exception\InvalidMerchantInformationException
     * @throws Exception\MissingMerchantInformationException
     * @throws InvalidChargeInformationException
     */
    public static function createRocketgateTransactionOtherPaymentInformation(
        ?string $siteId,
        ?string $billerName,
        CommandPayment $payment,
        ?OwnerInfo $ownerInfo,
        ?BillingAddress $billingAddress,
        ChargeInformation $chargeInformation,
        BillerSettings $billerFields,
        ?Transaction $previousTransaction,
        bool $useThreeD
    ): self {
        return new static(
            TransactionId::create(),
            $billerName,
            RocketGateChargeSettings::create(
                $billerFields->merchantId(),
                $billerFields->merchantPassword(),
                $billerFields->merchantCustomerId(),
                $billerFields->merchantInvoiceId(),
                $billerFields->merchantAccount(),
                $billerFields->merchantSiteId(),
                $billerFields->merchantProductId(),
                $billerFields->merchantDescriptor(),
                $billerFields->ipAddress(),
                $billerFields->referringMerchantId(),
                $billerFields->sharedSecret(),
                $billerFields->simplified3DS()
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            new CheckInformation(
                $payment->information()->routingNumber(),
                $payment->information()->accountNumber(),
                $payment->information()->savingAccount(),
                $payment->information()->socialSecurityLast4(),
                $ownerInfo,
                $billingAddress
            ),
            SiteId::createFromString($siteId),
            $chargeInformation,
            $payment->type(),
            new BillerInteractionCollection(),
            $useThreeD,
            null,
            null,
            $payment->information()->method()
        );
    }

    /**
     * @param string            $siteId            Site Id
     * @param string            $siteName          Site Name
     * @param string            $billerName        Biller Name
     * @param ChargeInformation $chargeInformation Charge Information
     * @param string            $paymentType       Payment Type
     * @param string            $paymentMethod     Payment Method
     * @param BillerSettings    $billerFields      Biller Fields
     * @param string|null       $username          Username
     * @param string|null       $password          Password
     *
     * @return Transaction|ChargeTransaction
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     */
    protected static function createEpochNewSaleTransaction(
        string $siteId,
        string $siteName,
        string $billerName,
        ChargeInformation $chargeInformation,
        string $paymentType,
        string $paymentMethod,
        BillerSettings $billerFields,
        ?string $username,
        ?string $password
    ): self {
        return new static(
            TransactionId::create(),
            $billerName,
            $billerFields,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            null,
            null,
            SiteId::createFromString($siteId),
            $chargeInformation,
            $paymentType,
            new BillerInteractionCollection(),
            false,
            null,
            BillerMember::create($username, $password),
            $paymentMethod,
            $siteName
        );
    }

    /**
     * @param string            $siteId            Site Id
     * @param string            $billerName        Biller Name
     * @param ChargeInformation $chargeInformation Charge Information
     * @param string            $paymentType       Payment Type
     * @param BillerSettings    $billerFields      Biller Fields
     * @param string            $paymentMethod     Payment Method
     * @param string|null       $username          Username
     * @param string|null       $password          Password
     *
     * @return Transaction|ChargeTransaction
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     */
    protected static function createLegacyNewSaleTransaction(
        string $siteId,
        string $billerName,
        ChargeInformation $chargeInformation,
        string $paymentType,
        BillerSettings $billerFields,
        ?string $paymentMethod,
        ?string $username,
        ?string $password
    ): self {
        return new static(
            TransactionId::create(),
            $billerName,
            $billerFields,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            null,
            null,
            SiteId::createFromString($siteId),
            $chargeInformation,
            $paymentType,
            new BillerInteractionCollection(),
            false,
            null,
            BillerMember::create($username, $password),
            $paymentMethod
        );
    }

    /**
     * @param string            $billerName        Biller Name
     * @param BillerSettings    $billerFields      Biller fields
     * @param string            $siteId            Site Id
     * @param ChargeInformation $chargeInformation Charge information
     *
     * @return ChargeTransaction
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     */
    protected static function createPumaPayChargeTransaction(
        string $billerName,
        BillerSettings $billerFields,
        string $siteId,
        ChargeInformation $chargeInformation
    ): self {
        return new static(
            TransactionId::create(),
            $billerName,
            $billerFields,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            null,
            null,
            SiteId::createFromString($siteId),
            $chargeInformation,
            PaymentType::CRYPTO,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param string|null                   $siteId                   The site id
     * @param string|null                   $billerName               The biller name
     * @param CommandPayment                $payment                  The payment object
     * @param CreditCardOwner|null          $creditCardOwner          The credit card owner object
     * @param CreditCardBillingAddress|null $creditCardBillingAddress The credit card billing address object
     * @param ChargeInformation             $chargeInformation        The charge information
     * @param BillerSettings                $billerFields             The biller fields
     * @param Transaction|null              $previousTransaction      The previous transaction
     * @param bool                          $shouldCheckDate          Should check if date is valid
     *
     * @return ChargeTransaction
     * @throws Exception\InvalidCreditCardCvvException
     * @throws Exception\InvalidCreditCardExpirationDateException
     * @throws Exception\InvalidCreditCardNumberException
     * @throws Exception\InvalidCreditCardTypeException
     * @throws Exception\InvalidMerchantInformationException
     * @throws Exception\InvalidPayloadException
     * @throws Exception\MissingInitialDaysException
     * @throws Exception\MissingMerchantInformationException
     * @throws InvalidChargeInformationException
     * @throws LoggerException
     */
    protected static function createNetbillingTransactionWithNewCreditCardInformation(
        ?string $siteId,
        ?string $billerName,
        CommandPayment $payment,
        ?CreditCardOwner $creditCardOwner,
        ?CreditCardBillingAddress $creditCardBillingAddress,
        ChargeInformation $chargeInformation,
        BillerSettings $billerFields,
        ?Transaction $previousTransaction,
        bool $shouldCheckDate = true
    ): self {
        /** @var NetbillingChargeSettings $billerFields */
        $netbillingChargeSettings = NetbillingChargeSettings::create(
            $billerFields->siteTag(),
            $billerFields->accountId(),
            $billerFields->merchantPassword(),
            $billerFields->initialDays(),
            $billerFields->ipAddress(),
            $billerFields->browser(),
            $billerFields->host(),
            $billerFields->description(),
            $billerFields->binRouting(),
            $billerFields->billerMemberId(),
            $billerFields->disableFraudChecks()
        );

        $creditCardInfo = CreditCardInformation::create(
            true, //should this be part of the payload?
            CreditCardNumber::create((string) $payment->information()->number()),
            $creditCardOwner,
            $creditCardBillingAddress,
            $payment->information()->cvv(),
            $payment->information()->expirationMonth(),
            $payment->information()->expirationYear(),
            $shouldCheckDate
        );

        return new static(
            TransactionId::create(),
            $billerName,
            $netbillingChargeSettings,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            $creditCardInfo,
            SiteId::createFromString($siteId),
            $chargeInformation,
            $payment->type(),
            new BillerInteractionCollection()
        );
    }

    /**
     * @param string|null       $siteId              The site id
     * @param string|null       $billerName          The biller name
     * @param CommandPayment    $payment             The payment object
     * @param ChargeInformation $chargeInformation   The charge information
     * @param BillerSettings    $billerFields        The biller fields
     * @param Transaction|null  $previousTransaction The previous transaction
     * @param BillerMember|null $billerMember        Login information
     *
     * @return static
     * @throws Exception\InvalidChargeInformationException
     * @throws Exception\InvalidCreditCardInformationException
     * @throws Exception\InvalidMerchantInformationException
     * @throws Exception\InvalidPayloadException
     * @throws Exception\MissingInitialDaysException
     * @throws Exception\MissingMerchantInformationException
     * @throws LoggerException
     */
    protected static function createNetbillingTransactionWithExistingCreditCardInformation(
        ?string $siteId,
        ?string $billerName,
        CommandPayment $payment,
        ChargeInformation $chargeInformation,
        BillerSettings $billerFields,
        ?Transaction $previousTransaction,
        ?BillerMember $billerMember = null
    ): self {

        /** @var NetbillingChargeSettings $billerFields */
        $netbillingChargeSettings = NetbillingChargeSettings::create(
            $billerFields->siteTag(),
            $billerFields->accountId(),
            $billerFields->merchantPassword(),
            $billerFields->initialDays(),
            $billerFields->ipAddress(),
            $billerFields->browser(),
            $billerFields->host(),
            $billerFields->description(),
            $billerFields->binRouting(),
            $billerFields->billerMemberId()
        );

        /** @var NetbillingPaymentTemplateInformation $paymentTemplateInfo */
        $paymentTemplateInfo = NetbillingPaymentTemplateInformation::create(
            NetbillingCardHash::create($payment->information()->cardHash())
        );

        return new static(
            TransactionId::create(),
            $billerName,
            $netbillingChargeSettings,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            $paymentTemplateInfo,
            SiteId::createFromString($siteId),
            $chargeInformation,
            $payment->type(),
            new BillerInteractionCollection(),
            false,
            null,
            $billerMember
        );
    }

    /**
     * @return string|null
     */
    public function legacyTransactionId(): ?string
    {
        return $this->legacyTransactionId;
    }

    /**
     * @return string|null
     */
    public function legacyMemberId(): ?string
    {
        return $this->legacyMemberId;
    }

    /**
     * @return string|null
     */
    public function legacySubscriptionId(): ?string
    {
        return $this->legacySubscriptionId;
    }

    /**
     * @return string|null
     */
    public function returnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /** TODO kept for backwards compatibility - billerId removal */
    /**
     * @return string
     */
    public function billerId(): string
    {
        if (!empty($this->billerId)) {
            return $this->billerId;
        }
        return (string) array_flip(BillerSettings::MAP_BILLER_IDS_TO_NAMES)[$this->billerName];
    }

    /** TODO updated for backwards compatibility */
    /**
     * @return string
     */
    public function billerName(): string
    {
        if (!empty($this->billerName)) {
            return $this->billerName;
        }

        return BillerSettings::MAP_BILLER_IDS_TO_NAMES[$this->billerId];
    }

    /**
     * @return DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return SiteId|null
     */
    public function siteId(): ?SiteId
    {
        return $this->siteId;
    }

    /**
     * @return string|null
     */
    public function siteName(): ?string
    {
        return $this->siteName;
    }

    /**
     * @return PaymentInformation
     */
    public function paymentInformation(): ?PaymentInformation
    {
        return $this->paymentInformation;
    }

    /**
     * @param PaymentInformation|null $paymentInformation Payment information
     * @return void
     */
    public function setPaymentInformation(?PaymentInformation $paymentInformation): void
    {
        $this->paymentInformation = $paymentInformation;
    }

    /**
     * @return BillerSettings|null
     */
    public function billerChargeSettings(): ?BillerSettings
    {
        return $this->billerChargeSettings;
    }

    /**
     * @return BillerMember|null
     */
    public function billerMember(): ?BillerMember
    {
        return $this->billerMember;
    }

    /**
     * @return bool
     */
    public function requiredToUse3D(): bool
    {
        return $this->requiredToUse3D;
    }

    /**
     * @return void
     * @throws IllegalStateTransitionException
     * @throws \Exception
     */
    public function refund(): void
    {
        $this->status = $this->status->refund();
    }

    /**
     * @return void
     * @throws IllegalStateTransitionException
     * @throws \Exception
     */
    public function chargeback(): void
    {
        $this->status = $this->status->chargeback();
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return (string) $this->transactionId();
    }

    /**
     * @return string|null
     */
    public function responsePayload(): ?string
    {
        /** @var BillerInteraction $billerInteraction */
        foreach ($this->billerInteractions() as $billerInteraction) {
            if ($billerInteraction->isResponseType()) {
                return $billerInteraction->payload();
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function billerInteractions()
    {
        return $this->billerInteractions;
    }

    /**
     * @return string|null
     */
    public function responsePayloadThreeDsTwo(): ?string
    {
        $interactions = $this->billerInteractions()->toArray();

        /**
         * Remove all request biller interactions
         * @var int               $key
         * @var BillerInteraction $interaction
         */
        foreach ($interactions as $key => $interaction) {
            if ($interaction->type() == BillerInteraction::TYPE_REQUEST) {
                unset($interactions[$key]);
            }
        }

        RocketgateBillerInteractionsReturnType::sortBillerInteractions($interactions);

        return (end($interactions) !== false) ? end($interactions)->payload() : null;
    }

    /**
     * @return bool
     */
    public function shouldReturn400()
    {
        return $this->shouldReturn400;
    }

    /**
     * @param string $code Code
     *
     * @return void
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return bool
     * @throws InvalidChargeInformationException
     * @throws Exception\InvalidChargeInformationException
     * @throws LoggerException
     */
    public function isFreeSale(): bool
    {
        if (empty($this->chargeInformation)) {
            return true;
        }

        return $this->chargeInformation->amount()->equals(Amount::create(0.0));
    }

    /**
     * Update a transaction based on the biller response
     *
     * @param RocketgateBillerResponse $billerResponse Rocketgate Biller Response
     *
     * @return void
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidThreedsVersionException
     * @throws LoggerException
     */
    public function updateRocketgateTransactionFromBillerResponse(RocketgateBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        // Add error classification based on the biller response.
        if ($billerResponse->declined() || $billerResponse->aborted()) {
            // Build mapping criteria based on biller response.
            $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);
            $this->setErrorMappingCriteria($mappingCriteria);
        }

        if ($this->isNsf && env('NSF_FLOW_ENABLED')) {
            $this->updateTransactionBillerInteraction($billerResponse);

            return;
        }

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if (env('NSF_FLOW_ENABLED')) {
            $this->isNsf = $billerResponse->isNsfTransaction();
        }

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->threeDsAuthIsRequired()
            || $billerResponse->threeDsInitIsRequired()
            || $billerResponse->threeDsScaIsRequired()
            || ($billerResponse->threedsVersion() !== null)
        ) {
            if (!$this->with3D()) {
                Log::warning("3DS AUTH required but purchase not done with use 3DS flag");
            } else {
                $this->updateTransactionWith3D(true);

                $threedsVersion = $billerResponse->threedsVersion();

                if (empty($threedsVersion)) {
                    Log::warning("Purchase with 3DS started but version not received on response");
                } else {
                    $this->updateThreedsVersion($threedsVersion);
                }
            }
        }

        if ($billerResponse->approved()) {
            $this->approve();
        } elseif ($billerResponse->declined()) {
            $this->shouldReturn400 = $billerResponse->shouldReturn400();
            $this->decline();
        }
    }

    /**
     * @param BillerResponse $billerResponse Biller Response
     *
     * @return void
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    public function updateTransactionBillerInteraction(BillerResponse $billerResponse): void
    {
        if (!empty($billerResponse->requestPayload())) {
            $this->addBillerInteraction(
                BillerInteraction::create(
                    BillerInteraction::TYPE_REQUEST,
                    $billerResponse->requestPayload(),
                    $billerResponse->requestDate(),
                    BillerInteractionId::create()
                )
            );
        }

        if (!empty($billerResponse->responsePayload())) {
            $this->addBillerInteraction(
                BillerInteraction::create(
                    BillerInteraction::TYPE_RESPONSE,
                    $billerResponse->responsePayload(),
                    new DateTimeImmutable(),
                    BillerInteractionId::create()
                )
            );
        }
    }

    /**
     * @param BillerInteraction $billerInteraction BillerInteraction object
     *
     * @return void|mixed
     */
    public function addBillerInteraction(
        BillerInteraction $billerInteraction
    ): void {
        $this->billerInteractions->add($billerInteraction);
    }

    /**
     * @return void
     * @throws IllegalStateTransitionException
     * @throws \Exception
     */
    public function abort(): void
    {
        $this->status = $this->status->abort();
    }

    /**
     * @return AbstractStatus
     */
    public function status(): AbstractStatus
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function code(): ?string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function reason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return TransactionId|null
     */
    public function previousTransactionId(): ?TransactionId
    {
        return $this->previousTransaction() ? $this->previousTransaction()->transactionId() : null;
    }

    /**
     * @return Transaction|null
     */
    public function previousTransaction(): ?Transaction
    {
        return $this->previousTransaction;
    }

    /**
     * @return TransactionId
     */
    public function transactionId(): TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return bool
     */
    public function with3D(): bool
    {
        return $this->with3D;
    }

    /**
     * @param bool $transactionWith3D If 3DS is used
     *
     * @return void
     */
    public function updateTransactionWith3D(bool $transactionWith3D): void
    {
        $this->with3D = $transactionWith3D;
    }

    /**
     * @param int $threedsVersion 3DS version
     *
     * @return void
     * @throws LoggerException
     * @throws InvalidThreedsVersionException
     */
    public function updateThreedsVersion(int $threedsVersion): void
    {
        if (empty($this->threedsVersion)) {
            $this->threedsVersion = $threedsVersion;

            return;
        }

        if ($threedsVersion === $this->threedsVersion) {
            return;
        }

        // version can be set from 2 to 1, but no from 1 to 2
        if ($threedsVersion < $this->threedsVersion) {
            $this->threedsVersion = $threedsVersion;

            return;
        }

        throw new InvalidThreedsVersionException($threedsVersion);
    }

    /**
     * @return void
     * @throws IllegalStateTransitionException
     * @throws \Exception
     */
    public function approve(): void
    {
        $this->status = $this->status->approve();
    }

    /**
     * @return string|null
     */
    public function paymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return int|null
     */
    public function threedsVersion(): ?int
    {
        return $this->threedsVersion;
    }

    /**
     * @return void
     * @throws IllegalStateTransitionException
     * @throws \Exception
     */
    public function decline(): void
    {
        $this->status = $this->status->decline();
    }

    /**
     * @param PumapayBillerResponse $billerResponse Biller response
     *
     * @return void
     * @throws Exception\InvalidBillerInteractionTypeException
     * @throws IllegalStateTransitionException
     * @throws LoggerException
     * @throws Exception\InvalidBillerInteractionPayloadException
     */
    public function updatePumapayTransactionFromBillerResponse(PumapayBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->declined()) {
            $this->decline();
        }

        if ($billerResponse->approved()) {
            $this->approve();
        }
    }

    /**
     * @param QyssoBillerResponse $billerResponse Biller response
     * @return void
     * @throws Exception\InvalidBillerInteractionPayloadException
     * @throws Exception\InvalidBillerInteractionTypeException
     * @throws IllegalStateTransitionException
     * @throws LoggerException
     */
    public function updateQyssoTransactionFromBillerResponse(QyssoBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->declined()) {
            $this->decline();
        }

        if ($billerResponse->approved()) {
            $this->approve();
        }
    }

    /**
     * @param EpochBillerResponse $billerResponse Biller response
     *
     * @return void
     * @throws Exception\InvalidBillerInteractionPayloadException
     * @throws Exception\InvalidBillerInteractionTypeException
     * @throws IllegalStateTransitionException
     * @throws LoggerException
     */
    public function updateEpochTransactionFromBillerResponse(EpochBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->declined()) {
            $this->decline();
        }

        if ($billerResponse->approved()) {
            $this->approve();
        }

        if ($billerResponse instanceof EpochPostbackBillerResponse) {
            $this->paymentMethod = $billerResponse->paymentMethod();
            $this->paymentType   = $billerResponse->paymentType();
        }
    }

    /**
     * @param LegacyBillerResponse $billerResponse Biller Response
     *
     * @return void
     * @throws Exception\InvalidBillerInteractionTypeException
     * @throws IllegalStateTransitionException
     * @throws LoggerException
     * @throws Exception\InvalidBillerInteractionPayloadException
     */
    public function updateLegacyTransactionFromBillerResponse(LegacyBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating legacy transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->declined()) {
            $this->decline();
        }

        if ($billerResponse->approved()) {
            $this->approve();
            $this->updateChargeInformationAccordingThirdPartyBillerResponse($billerResponse);
        }
    }

    /**
     * @param LegacyBillerResponse $billerResponse Biller Response
     *
     * @return void
     */
    private function updateChargeInformationAccordingThirdPartyBillerResponse(
        LegacyBillerResponse $billerResponse
    ): void {
        if ($billerResponse instanceof LegacyPostbackBillerResponse) {
            $this->chargeInformation = ChargeInformation::create(
                $billerResponse->amount() ?? $this->chargeInformation->amount(),
                $this->chargeInformation()->currency(),
                $billerResponse->rebill() ?? $this->chargeInformation->rebill()
            );

            $this->legacyTransactionId  = $billerResponse->legacyTransactionId();
            $this->legacyMemberId       = $billerResponse->legacyMemberId();
            $this->legacySubscriptionId = $billerResponse->legacySubscriptionId();
        }
    }

    /**
     * @return ChargeInformation|null
     */
    public function chargeInformation(): ?ChargeInformation
    {
        return $this->chargeInformation;
    }

    /**
     * Update a transaction based on the biller response
     *
     * @param NetbillingBillerResponse $billerResponse Netbilling Biller Response
     *
     * @return void
     * @throws Exception\InvalidBillerInteractionTypeException
     * @throws IllegalStateTransitionException
     * @throws LoggerException
     *
     * @throws Exception\InvalidBillerInteractionPayloadException
     */
    public function updateTransactionFromNetbillingResponse(NetbillingBillerResponse $billerResponse): void
    {
        Log::info(
            'Updating transaction status and adding biller interactions',
            ['transactionId' => (string) $this->transactionId()]
        );

        if ($this->isNsf && env('NSF_FLOW_ENABLED')) {
            $this->updateTransactionBillerInteraction($billerResponse);

            return;
        }

        $this->code   = $billerResponse->code();
        $this->reason = $billerResponse->reason();

        if (env('NSF_FLOW_ENABLED')) {
            $this->isNsf = $billerResponse->isNsfTransaction();
        }

        if ($billerResponse->aborted()) {
            $this->abort();
        }

        $this->updateTransactionBillerInteraction($billerResponse);

        if ($billerResponse->approved()) {
            $this->approve();
        } elseif ($billerResponse->declined()) {
            $this->shouldReturn400 = $billerResponse->shouldReturn400();
            $this->decline();
        }
    }

    /**
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     */
    public function updateChargeAmountToFreeJoin(): void
    {
        $chargeInfo = $this->chargeInformation();

        $freeSaleCharge = ChargeInformation::createSingleCharge(
            $chargeInfo->currency(),
            Amount::create(0.00)
        );

        $this->chargeInformation = $freeSaleCharge;
    }

    /**
     * @return array|null
     */
    public function subsequentOperationFieldsToArray(): ?array
    {
        if ($this->subsequentOperationFields !== null) {
            return json_decode($this->subsequentOperationFields, true);
        }

        return null;
    }

    /**
     * @param MappingCriteria|null $mappingCriteria Mapping criteria.
     * @return void
     */
    public function setErrorMappingCriteria(?MappingCriteria $mappingCriteria = null): void
    {
        $this->errorMappingCriteria = $mappingCriteria;
    }

    /**
     * @param string $id Transaction id.
     * @return void
     * @throws InvalidTransactionInformationException
     */
    public function setTransactionId(string $id): void
    {
        $this->transactionId = TransactionId::createFromString($id);
    }

    /**
     * @param string $status Status.
     * @return void
     * @throws \Exception
     */
    public function setStatus(string $status): void
    {
        $this->status = AbstractStatus::createFromString($status);
    }

    /**
     * @param Timestamp $createdAt Created at.
     * @return void
     * @throws \Exception
     */
    public function setCreatedAt(Timestamp $createdAt): void
    {
        $this->createdAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $createdAt->get()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param Timestamp $updatedAt Updated at.
     * @return void
     */
    public function setUpdatedAt(Timestamp $updatedAt): void
    {
        $this->updatedAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $updatedAt->get()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param int|null $version Three DS version.
     * @return void
     */
    public function setThreeDSVersion(?int $version): void
    {
        $this->threedsVersion = $version;
    }

    /**
     * @param string|null $legacyMemberId Legacy member id.
     * @return void
     */
    public function setLegacyMemberId(?string $legacyMemberId): void
    {
        $this->legacyMemberId = $legacyMemberId;
    }

    /**
     * @param string|null $legacySubscriptionId Legacy subscription id.
     * @return void
     */
    public function setLegacySubscriptionId(?string $legacySubscriptionId): void
    {
        $this->legacySubscriptionId = $legacySubscriptionId;
    }

    /**
     * @param string|null $legacyTransactionId Legacy transaction id.
     * @return void
     */
    public function setLegacyTransactionId(?string $legacyTransactionId): void
    {
        $this->legacyTransactionId = $legacyTransactionId;
    }

    /**
     * @param string|null $subsequentOperationFields Subsequent operations fields.
     * @return void
     */
    public function setSubsequentOperationFields(?string $subsequentOperationFields): void
    {
        $this->subsequentOperationFields = $subsequentOperationFields;
    }

    /**
     * @param string|null $billerTransactions Biller transactions.
     * @return void
     */
    public function setBillerTransactions(?string $billerTransactions): void
    {
        $this->billerTransactions = $billerTransactions;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $billerInteractions = null;

        if ($this->billerInteractions() !== null) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($this->billerInteractions() as $billerInteraction) {
                $billerInteractions[] = $billerInteraction->toArray();
            }
        }

        return [
            'transactionId'                   => $this->getEntityId(),
            'billerName'                      => $this->billerName(),
            'billerId'                        => $this->billerId(),
            'siteId'                          => (string) $this->siteId(),
            'siteName'                        => $this->siteName(),
            'billerChargeSettings'            => $this->billerChargeSettings() !== null ? $this->billerChargeSettings()
                ->toArray() : null,
            'paymentInformation'              => $this->paymentInformation() !== null ? $this->paymentInformation()
                ->toArray() : null,
            'chargeInformation'               => $this->chargeInformation() !== null ? $this->chargeInformation()
                ->toArray() : null,
            'status'                          => (string) $this->status(),
            'paymentType'                     => $this->paymentType(),
            'updatedAt'                       => new Timestamp(DateTime::createFromImmutable($this->updatedAt)),
            'createdAt'                       => new Timestamp(DateTime::createFromImmutable($this->createdAt)),
            'previousTransactionId'           => !empty($this->previousTransactionId()) ? (string) $this->previousTransactionId() : null,
            'originalTransactionId'           => $this->originalTransactionId,
            'paymentMethod'                   => $this->paymentMethod(),
            'threedsVersion'                  => $this->threedsVersion() ?? 0,
            'legacyTransactionId'             => $this->legacyTransactionId(),
            'legacyMemberId'                  => $this->legacyMemberId(),
            'legacySubscriptionId'            => $this->legacySubscriptionId(),
            'isNsf'                           => $this->isNsf,
            'billerTransactions'              => !empty($this->billerTransactions) ? json_decode($this->billerTransactions,
                true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR) : null,
            'subsequentOperationFields'       => !empty($this->subsequentOperationFields) ? json_decode($this->subsequentOperationFields,
                true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR) : null,
            'subsequentOperationFieldsLegacy' => $this->subsequentOperationFieldsLegacy,
            'isPrimaryCharge'                 => $this->isPrimaryCharge,
            'chargeId'                        => $this->chargeId,
            'version'                         => $this->version,
            'billerInteractions'              => $billerInteractions,
            'returnUrl'                       => $this->returnUrl,
        ];
    }
}
