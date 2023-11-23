<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\OtherPaymentTypeInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment as CommandPayment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill as CommandRebill;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebill;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;

class ChargeTransaction extends Transaction
{
    public const TYPE = 'charge';

    /**
     * ChargeTransaction constructor.
     *
     * @param TransactionId                    $transactionId        Transaction Id
     * @param string                           $billerName           Biller Id
     * @param BillerSettings                   $billerChargeSettings Biller Charge Settings
     * @param DateTimeImmutable                $createdAt            Created At
     * @param DateTimeImmutable                $updatedAt            Updated At
     * @param AbstractStatus                   $status               Status
     * @param Transaction|null                 $previousTransaction  Previous Transaction
     * @param PaymentInformation|null          $paymentInformation   Payment Information
     * @param SiteId|null                      $siteId               Site Id
     * @param ChargeInformation|null           $chargeInformation    Charge Information
     * @param string|null                      $paymentType          Payment Type
     * @param BillerInteractionCollection|null $billerInteractions   Biller Interactions
     * @param bool                             $requiredToUse3D      Is 3D secure triggered
     * @param string|null                      $returnUrl            The 3ds return url
     * @param BillerMember|null                $billerMember         Biller Member Login information
     * @param string|null                      $paymentMethod        Payment Method
     * @param string|null                      $siteName             Site Name
     *
     * @throws BillerSettingObfuscatorNotDefined
     * @throws Exception
     * @throws InvalidPaymentTypeException
     */
    protected function __construct(
        TransactionId $transactionId,
        string $billerName,
        BillerSettings $billerChargeSettings,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        AbstractStatus $status,
        ?Transaction $previousTransaction,
        ?PaymentInformation $paymentInformation,
        ?SiteId $siteId,
        ?ChargeInformation $chargeInformation,
        ?string $paymentType,
        ?BillerInteractionCollection $billerInteractions,
        bool $requiredToUse3D = false,
        ?string $returnUrl = null,
        ?BillerMember $billerMember = null,
        ?string $paymentMethod = null,
        ?string $siteName = null
    ) {
        $this->transactionId        = $transactionId;
        $this->siteId               = $siteId;
        $this->billerName           = $billerName;
        $this->paymentInformation   = $paymentInformation;
        $this->chargeInformation    = $chargeInformation;
        $this->billerChargeSettings = $billerChargeSettings;
        $this->createdAt            = $createdAt;
        $this->updatedAt            = $updatedAt;
        $this->status               = $status;
        $this->billerInteractions   = $billerInteractions;
        $this->initPaymentType($paymentType);
        $this->paymentMethod       = $paymentMethod;
        $this->previousTransaction = $previousTransaction;
        $this->requiredToUse3D     = $requiredToUse3D;
        $this->with3D              = $requiredToUse3D;
        $this->billerMember        = $billerMember;
        $this->siteName            = $siteName;
        $this->threedsVersion      = null;
        $this->returnUrl           = $returnUrl;

        /** TODO kept for backwards compatibility - billerId removal */
        $this->billerId = $this->billerId();

        Log::info('New transaction entity created', ['transactionId' => (string) $transactionId]);
    }

    /**
     * @param string|null $paymentType Payment type (method)
     * @return void
     * @throws Exception
     * @throws InvalidPaymentTypeException
     */
    private function initPaymentType(?string $paymentType): void
    {
        if (empty($paymentType)) {
            //if no payment type is provided, assume it's a rebill update operation
            return;
        }

        $this->paymentType = (string) PaymentType::create($paymentType);
    }

    /**
     * @param string            $siteId            Site Id
     * @param string            $billerName        Biller Name
     * @param ChargeInformation $chargeInformation Charge Information
     * @param string            $paymentType       Payment Type
     * @param BillerSettings    $billerSettings    Biller Fields
     * @param string            $paymentMethod     Payment Method
     * @param string|null       $username          Username
     * @param string|null       $password          Password
     *
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    public static function createTransactionOnLegacy(
        string $siteId,
        string $billerName,
        ChargeInformation $chargeInformation,
        string $paymentType,
        BillerSettings $billerSettings,
        ?string $paymentMethod,
        ?string $username,
        ?string $password
    ): self {
        return self::createLegacyNewSaleTransaction(
            $siteId,
            $billerName,
            $chargeInformation,
            $paymentType,
            $billerSettings,
            $paymentMethod,
            $username,
            $password
        );
    }

    /**
     * Create the transaction entity without rebill
     *
     * @param string|null         $siteId          Site Id
     * @param float|null          $amount          Amount
     * @param string|null         $billerName      Biller Id
     * @param string|null         $currency        Currency
     * @param CommandPayment|null $payment         Payment
     * @param BillerSettings      $billerFields    Biller fields
     * @param bool                $requiredToUse3D Is threeD secure triggered
     * @param string|null         $returnUrl       The 3ds return url
     *
     * @return self
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public static function createSingleChargeOnRocketgate(
        ?string $siteId,
        ?float $amount,
        ?string $billerName,
        ?string $currency,
        ?CommandPayment $payment,
        BillerSettings $billerFields,
        bool $requiredToUse3D = false,
        ?string $returnUrl = null
    ): self {
        $chargeInformation = ChargeInformation::createSingleCharge(
            Currency::create($currency),
            Amount::create($amount)
        );

        return self::createTransactionOnRocketgate(
            $billerName,
            $billerFields,
            $siteId,
            $payment,
            $chargeInformation,
            null,
            $requiredToUse3D,
            $returnUrl
        );
    }

    /**
     * @param string                 $billerName          Biller Id
     * @param BillerSettings         $billerFields        Biller fields
     * @param string|null            $siteId              Site Id
     * @param CommandPayment|null    $payment             Payment
     * @param ChargeInformation|null $chargeInformation   Charge information
     * @param Transaction|null       $previousTransaction Previous transaction
     * @param bool                   $requiredToUse3D     Three D is triggered of not
     * @param string|null            $returnUrl           The 3ds return url
     * @param bool                   $shouldCheckDate     Should check if date is valid
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    public static function createTransactionOnRocketgate(
        string $billerName,
        BillerSettings $billerFields,
        ?string $siteId = null,
        ?CommandPayment $payment = null,
        ?ChargeInformation $chargeInformation = null,
        ?Transaction $previousTransaction = null,
        bool $requiredToUse3D = false,
        ?string $returnUrl = null,
        bool $shouldCheckDate = true
    ): self {
        // if we have card hash create the specific a transaction with the specific payment information
        if ($payment !== null && $payment->information() instanceof ExistingCreditCardInformation) {
            return self::createRocketgateTransactionWithExistingCreditCardInformation(
                $siteId,
                $billerName,
                $payment,
                $chargeInformation,
                $billerFields,
                $previousTransaction,
                $requiredToUse3D,
                $returnUrl
            );
        }

        // We should create a factory to return payment information
        // instead 3 create methods. Due time constraints and bkw compatibility we decided
        // to keep in this way.
        if ($payment !== null && $payment->information() instanceof OtherPaymentTypeInformation) {
            return self::createRocketgateTransactionOtherPaymentInformation(
                $siteId,
                $billerName,
                $payment,
                self::createAccountOwner($payment),
                self::createCustomerBillingAddress($payment),
                $chargeInformation,
                $billerFields,
                $previousTransaction,
                $requiredToUse3D
            );
        }

        return self::createRocketgateTransactionWithNewCreditCardInformation(
            $siteId,
            $billerName,
            $payment,
            self::createCreditCardOwner($payment),
            self::createCreditCardBillingAddress($payment),
            $chargeInformation,
            $billerFields,
            $previousTransaction,
            $requiredToUse3D,
            $returnUrl,
            $shouldCheckDate
        );
    }

    /**
     * @param CommandPayment $payment The payment object
     * @return CreditCardOwner|null
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     */
    private static function createCreditCardOwner(CommandPayment $payment): ?CreditCardOwner
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return CreditCardOwner::create(
                $payment->information()->member()->firstName(),
                $payment->information()->member()->lastName(),
                Email::create($payment->information()->member()->email()),
                $payment->information()->member()->userName(),
                $payment->information()->member()->password()
            );
        }

        return null;
    }

    /**
     * @param CommandPayment $payment The payment object
     *
     * @return AccountOwner|null
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     */
    private static function createAccountOwner(CommandPayment $payment): ?AccountOwner
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return AccountOwner::create(
                $payment->information()->member()->firstName(),
                $payment->information()->member()->lastName(),
                Email::create($payment->information()->member()->email()),
                $payment->information()->member()->userName(),
                $payment->information()->member()->password()
            );
        }

        return null;
    }

    /**
     * @param CommandPayment $payment The payment object
     * @return CreditCardBillingAddress|null
     */
    private static function createCreditCardBillingAddress(CommandPayment $payment): ?CreditCardBillingAddress
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return CreditCardBillingAddress::create(
                $payment->information()->member()->address(),
                $payment->information()->member()->city(),
                $payment->information()->member()->country(),
                $payment->information()->member()->state(),
                $payment->information()->member()->zipCode(),
                $payment->information()->member()->phone()
            );
        }

        return null;
    }

    /**
     * @param CommandPayment $payment The payment object
     *
     * @return CustomerBillingAddress|null
     */
    private static function createCustomerBillingAddress(CommandPayment $payment): ?CustomerBillingAddress
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return CustomerBillingAddress::create(
                $payment->information()->member()->address(),
                $payment->information()->member()->city(),
                $payment->information()->member()->country(),
                $payment->information()->member()->state(),
                $payment->information()->member()->zipCode(),
                $payment->information()->member()->phone()
            );
        }

        return null;
    }

    /**
     * Create the transaction entity with rebill
     *
     * @param string|null    $siteId          Site Id
     * @param float|null     $amount          Amount
     * @param string|null    $billerName      Biller Id
     * @param string|null    $currency        Currency
     * @param CommandPayment $payment         Payment
     * @param BillerSettings $billerFields    Biller fields
     * @param CommandRebill  $rebill          Rebill
     * @param bool           $requiredToUse3D Is threeD secure triggered
     * @param null|string    $returnUrl       The 3ds return url
     *
     * @return self
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public static function createWithRebillOnRocketgate(
        ?string $siteId,
        ?float $amount,
        ?string $billerName,
        ?string $currency,
        CommandPayment $payment,
        BillerSettings $billerFields,
        CommandRebill $rebill,
        bool $requiredToUse3D = false,
        ?string $returnUrl = null
    ): self {
        $chargeInformation = ChargeInformation::createWithRebill(
            Currency::create($currency),
            Amount::create($amount),
            Rebill::create(
                $rebill->frequency(),
                $rebill->start(),
                Amount::create($rebill->amount())
            )
        );

        return self::createTransactionOnRocketgate(
            $billerName,
            $billerFields,
            $siteId,
            $payment,
            $chargeInformation,
            null,
            $requiredToUse3D,
            $returnUrl
        );
    }

    /**
     * @param string         $siteId        Site Id
     * @param string         $siteName      Site Name
     * @param string         $billerName    Biller Id
     * @param float          $amount        Amount
     * @param string         $currency      Currency
     * @param string         $paymentType   Payment Type
     * @param string         $paymentMethod Payment Method
     * @param BillerSettings $billerFields  Biller Fields
     * @param string|null    $username      Username
     * @param string|null    $password      Password
     *
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    public static function createSingleChargeOnEpoch(
        string $siteId,
        string $siteName,
        string $billerName,
        float $amount,
        string $currency,
        string $paymentType,
        string $paymentMethod,
        BillerSettings $billerFields,
        ?string $username,
        ?string $password
    ): self {
        $chargeInformation = ChargeInformation::createSingleCharge(
            Currency::create($currency),
            Amount::create($amount)
        );

        return self::createTransactionOnEpoch(
            $siteId,
            $siteName,
            $billerName,
            $chargeInformation,
            $paymentType,
            $paymentMethod,
            $billerFields,
            $username,
            $password
        );
    }

    /**
     * @param string            $siteId            Site Id
     * @param string            $siteName          Site Name
     * @param string            $billerName        Biller Id
     * @param ChargeInformation $chargeInformation Charge Information
     * @param string            $paymentType       Payment Type
     * @param string            $paymentMethod     Payment Method
     * @param BillerSettings    $billerFields      Biller Fields
     * @param string|null       $username          Username
     * @param string|null       $password          Password
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    private static function createTransactionOnEpoch(
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
        return self::createEpochNewSaleTransaction(
            $siteId,
            $siteName,
            $billerName,
            $chargeInformation,
            $paymentType,
            $paymentMethod,
            $billerFields,
            $username,
            $password
        );
    }

    /**
     * @param string         $siteId        Site ID
     * @param string         $siteName      Site Name
     * @param string         $billerName    Biller Id
     * @param float          $amount        Amount
     * @param string         $currency      Currency
     * @param string         $paymentType   Payment Type
     * @param string         $paymentMethod Payment Method
     * @param BillerSettings $billerFields  Biller Fields
     * @param CommandRebill  $rebill        Rebill
     * @param string|null    $username      Username
     * @param string|null    $password      Password
     *
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    public static function createWithRebillOnEpoch(
        string $siteId,
        string $siteName,
        string $billerName,
        float $amount,
        string $currency,
        string $paymentType,
        string $paymentMethod,
        BillerSettings $billerFields,
        CommandRebill $rebill,
        ?string $username,
        ?string $password
    ): self {
        $chargeInformation = ChargeInformation::createWithRebill(
            Currency::create($currency),
            Amount::create($amount),
            Rebill::create(
                $rebill->frequency(),
                $rebill->start(),
                Amount::create($rebill->amount())
            )
        );

        return self::createTransactionOnEpoch(
            $siteId,
            $siteName,
            $billerName,
            $chargeInformation,
            $paymentType,
            $paymentMethod,
            $billerFields,
            $username,
            $password
        );
    }

    /**
     * @param string|null        $siteId        Site Id
     * @param float|null         $amount        Amount
     * @param string|null        $billerName    Biller Id
     * @param string|null        $currency      Currency
     * @param string|null        $businessId    Business Id
     * @param string|null        $businessModel Business model
     * @param string|null        $apiKey        Api key
     * @param string|null        $title         Title
     * @param string|null        $description   Description
     * @param CommandRebill|null $rebill        Rebill info
     *
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    public static function createSingleChargeOnPumapay(
        ?string $siteId,
        ?float $amount,
        ?string $billerName,
        ?string $currency,
        ?string $businessId,
        ?string $businessModel,
        ?string $apiKey,
        ?string $title,
        ?string $description,
        ?CommandRebill $rebill
    ): self {
        if ($rebill !== null) {
            $chargeInformation = ChargeInformation::createWithRebill(
                Currency::create($currency),
                Amount::create($amount),
                Rebill::create(
                    $rebill->frequency(),
                    $rebill->start(),
                    Amount::create($rebill->amount())
                )
            );
        } else {
            $chargeInformation = ChargeInformation::createSingleCharge(
                Currency::create($currency),
                Amount::create($amount)
            );
        }

        $billerFields = PumaPayChargeSettings::create(
            $businessId,
            $businessModel,
            $apiKey,
            $title,
            $description
        );

        return self::createPumaPayChargeTransaction(
            $billerName,
            $billerFields,
            $siteId,
            $chargeInformation
        );
    }

    /**
     * Create the Netbilling transaction entity without rebill
     *
     * @param string|null         $siteId          Site Id
     * @param float|null          $amount          Amount
     * @param string|null         $billerName      Biller Id
     * @param string|null         $currency        Currency
     * @param CommandPayment|null $payment         Payment
     * @param BillerSettings      $billerFields    Biller fields
     * @param BillerLoginInfo     $billerLoginInfo Login information
     *
     * @return self
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     */
    public static function createSingleChargeOnNetbilling(
        ?string $siteId,
        ?float $amount,
        ?string $billerName,
        ?string $currency,
        ?CommandPayment $payment,
        BillerSettings $billerFields,
        ?BillerLoginInfo $billerLoginInfo = null
    ): self {
        $billerMember      = null;
        $chargeInformation = ChargeInformation::createSingleCharge(
            Currency::create($currency),
            Amount::create($amount)
        );

        if (!empty($billerLoginInfo)) {
            $billerMember = BillerMember::create($billerLoginInfo->userName(), $billerLoginInfo->password());
        }

        return self::createTransactionOnNetbilling(
            $billerName,
            $billerFields,
            $siteId,
            $payment,
            $chargeInformation,
            null,
            $billerMember
        );
    }

    /**
     * @param string                 $billerName          Biller Id
     * @param BillerSettings         $billerFields        Biller fields
     * @param string|null            $siteId              Site Id
     * @param CommandPayment|null    $payment             Payment
     * @param ChargeInformation|null $chargeInformation   Charge information
     * @param Transaction|null       $previousTransaction Previous transaction
     * @param BillerMember|null      $billerMember        Login information
     * @param bool                   $shouldCheckDate     Should check if date is valid
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     */
    public static function createTransactionOnNetbilling(
        string $billerName,
        BillerSettings $billerFields,
        ?string $siteId = null,
        ?CommandPayment $payment = null,
        ?ChargeInformation $chargeInformation = null,
        ?Transaction $previousTransaction = null,
        ?BillerMember $billerMember = null,
        bool $shouldCheckDate = true
    ): self {
        if ($payment != null && $payment->information() instanceof ExistingCreditCardInformation) {
            return self::createNetbillingTransactionWithExistingCreditCardInformation(
                $siteId,
                $billerName,
                $payment,
                $chargeInformation,
                $billerFields,
                $previousTransaction,
                $billerMember
            );
        }
        return self::createNetbillingTransactionWithNewCreditCardInformation(
            $siteId,
            $billerName,
            $payment,
            self::createCreditCardOwner($payment),
            self::createCreditCardBillingAddress($payment),
            $chargeInformation,
            $billerFields,
            $previousTransaction,
            $shouldCheckDate
        );
    }

    /**
     * Create the Netbilling transaction entity with rebill
     *
     * @param string|null     $siteId          Site Id
     * @param float|null      $amount          Amount
     * @param string|null     $billerName      Biller Id
     * @param string|null     $currency        Currency
     * @param CommandPayment  $payment         Payment
     * @param BillerSettings  $billerFields    Biller fields
     * @param CommandRebill   $rebill          Rebill
     * @param BillerLoginInfo $billerLoginInfo Login information
     *
     * @return self
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     */
    public static function createWithRebillOnNetbilling(
        ?string $siteId,
        ?float $amount,
        ?string $billerName,
        ?string $currency,
        CommandPayment $payment,
        BillerSettings $billerFields,
        CommandRebill $rebill,
        ?BillerLoginInfo $billerLoginInfo = null
    ): self {
        $billerMember      = null;
        $chargeInformation = ChargeInformation::createWithRebill(
            Currency::create($currency),
            Amount::create($amount),
            NetbillingRebill::create(
                $rebill->frequency(),
                $rebill->start(),
                Amount::create($rebill->amount())
            )
        );

        if (!empty($billerLoginInfo)) {
            $billerMember = BillerMember::create($billerLoginInfo->userName(), $billerLoginInfo->password());
        }

        return self::createTransactionOnNetbilling(
            $billerName,
            $billerFields,
            $siteId,
            $payment,
            $chargeInformation,
            null,
            $billerMember
        );
    }

    /**
     * @param ChargeTransaction            $transaction    Transaction
     * @param LegacyPostbackBillerResponse $billerResponse Biller Response
     * @param string|null                  $siteId         Site Id
     *
     * @return ChargeTransaction
     * @throws BillerSettingObfuscatorNotDefined
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidPaymentTypeException
     */
    public static function createLegacyCrossSaleTransaction(
        ChargeTransaction $transaction,
        LegacyPostbackBillerResponse $billerResponse,
        string $siteId
    ): self {
        $userName = ($transaction->billerMember() !== null ? $transaction->billerMember()->userName() : null);
        $password = ($transaction->billerMember() !== null ? $transaction->billerMember()->password() : null);

        $now = new DateTimeImmutable('now');

        return new static(
            TransactionId::create(),
            $transaction->billerName(),
            $transaction->billerChargeSettings(),
            $now,
            $now,
            Pending::create(),
            null,
            null,
            SiteId::createFromString($siteId),
            ChargeInformation::create(
                $billerResponse->amount(),
                $transaction->chargeInformation()->currency(),
                $billerResponse->rebill()
            ),
            $transaction->paymentType(),
            new BillerInteractionCollection(),
            false,
            null,
            BillerMember::create($userName, $password),
            $transaction->paymentMethod()
        );
    }

    /**
     * @param int $mainProduct Main purchase legacy product id
     * @return void
     */
    public function addCustomFieldsToLegacyBillerSetting(int $mainProduct): void
    {
        $billerChargeSetting = $this->billerChargeSettings();
        if ($billerChargeSetting instanceof LegacyBillerChargeSettings) {
            $billerChargeSetting->addTransactionIdToCustomFields((string) $this->transactionId());
            $billerChargeSetting->addMainProductIdToCustomFields($mainProduct);
            $this->updateBillerChargeSettings($billerChargeSetting);
        }
    }

    /**
     * @param BillerSettings $billerChargeSettings Biller charge settings
     *
     * @return self
     */
    public function updateBillerChargeSettings(BillerSettings $billerChargeSettings): self
    {
        $this->billerChargeSettings = $billerChargeSettings;

        return $this;
    }

    /**
     * @param string             $siteId        SiteId.
     * @param float              $amount        Amount.
     * @param string             $currency      Currency
     * @param string             $businessId    Business Id.
     * @param string             $businessModel Business Model
     * @param string             $apiKey        Api Key.
     * @param string             $title         Title.
     * @param string|null        $description   Description.
     * @param CommandRebill|null $rebill        Rebill.
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    public function updatePumapayToReceiveQrCode(
        string $siteId,
        float $amount,
        string $currency,
        string $businessId,
        string $businessModel,
        string $apiKey,
        string $title,
        string $description,
        ?CommandRebill $rebill
    ): void {

        $this->billerName = PumaPayChargeSettings::PUMAPAY;

        $this->chargeInformation = ChargeInformation::createFromCommand($amount, $currency, $rebill);

        $this->siteId = SiteId::createFromString($siteId);

        $this->updateBillerChargeSettings(
            PumaPayChargeSettings::create(
                $businessId,
                $businessModel,
                $apiKey,
                $title,
                $description
            )
        );
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return 'transaction';
    }

    /**
     * @return bool
     */
    public function isNotProcessed(): bool
    {
        if (!$this->status()->pending()) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'type' => self::TYPE
            ]
        );
    }
}
