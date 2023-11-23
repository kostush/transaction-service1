<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Event;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\BillerSettingsObfuscator;
use ProBillerNG\Transaction\Domain\Model\BillerSettingsObfuscatorFactory;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\Rebill;

class TransactionCreatedEvent extends BaseEvent implements ObfuscatedData
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $billerName;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $siteId;

    /**
     * @var string|null
     */
    private $cvv2Check;

    /**
     * @var string|null
     */
    private $creditCardNumber;

    /**
     * @var string|null
     */
    private $ownerFirstName;

    /**
     * @var string|null
     */
    private $ownerLastName;

    /**
     * @var string|null
     */
    private $ownerEmail;

    /**
     * @var string|null
     */
    private $ownerAddress;

    /**
     * @var string|null
     */
    private $ownerCity;

    /**
     * @var string|null
     */
    private $ownerCountry;

    /**
     * @var string|null
     */
    private $ownerState;

    /**
     * @var string|null
     */
    private $ownerZip;

    /**
     * @var string|null
     */
    private $ownerPhoneNo;

    /**
     * @var string|null
     */
    private $cvv;

    /**
     * @var string|null
     */
    private $expirationMonth;

    /**
     * @var string|null
     */
    private $expirationYear;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string|null
     */
    private $rebillFrequency;

    /**
     * @var string|null
     */
    private $rebillStart;

    /**
     * @var string|null
     */
    private $rebillAmount;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var string|null
     */
    private $firstSix;

    /**
     * @var string|null
     */
    private $lastFour;

    /**
     * @var string
     */
    private $cardHash;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string|null
     */
    private $paymentMethod;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var array
     */
    private $billerSettings;

    /**
     * @var string|null
     */
    private $previousTransactionId;

    /**
     * @var int|null
     */
    private $threedsVersion;

    /**
     * TransactionCreatedEvent constructor.
     * @param string                  $transactionType       The transaction class that issued the event
     * @param string                  $aggregateRootId       The transaction id
     * @param string                  $billerName            The biller name
     * @param BillerSettings|null     $billerChargeSettings  The biller settings object
     * @param string                  $status                Current status
     * @param \DateTimeImmutable      $createdAt             Created at
     * @param \DateTimeImmutable      $updatedAt             Updated at
     * @param \DateTimeImmutable|null $occurredOn            Occurred at
     * @param string|null             $paymentType           The payment type used
     * @param string|null             $siteId                The site id
     * @param PaymentInformation|null $paymentInformation    The payment information object
     * @param ChargeInformation|null  $chargeInformation     The charge information
     * @param string|null             $code                  Biller response code
     * @param string|null             $reason                Biller response reason
     * @param string|null             $previousTransactionId Previous transaction id
     * @param string|null             $paymentMethod         Payment method
     * @param int|null                $threedsVersion        Threeds Version
     * @throws Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined
     */
    public function __construct(
        string $transactionType,
        string $aggregateRootId,
        string $billerName,
        ?BillerSettings $billerChargeSettings,
        string $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $occurredOn,
        ?string $paymentType,
        ?string $siteId,
        ?PaymentInformation $paymentInformation,
        ?ChargeInformation $chargeInformation,
        ?string $code,
        ?string $reason,
        ?string $previousTransactionId,
        ?string $paymentMethod,
        ?int $threedsVersion
    ) {
        parent::__construct($aggregateRootId, $occurredOn, $transactionType);

        $this->transactionId         = $aggregateRootId;
        $this->billerName            = $billerName;
        $this->siteId                = $siteId;
        $this->createdAt             = $createdAt;
        $this->updatedAt             = $updatedAt;
        $this->status                = $status;
        $this->code                  = $code;
        $this->reason                = $reason;
        $this->paymentType           = $paymentType;
        $this->previousTransactionId = $previousTransactionId;
        $this->paymentMethod         = $paymentMethod;
        $this->threedsVersion        = $threedsVersion;

        $this->setCreditCardInformation($paymentInformation);
        $this->setChargeInformation($chargeInformation);
        $this->setBillerSettings($billerChargeSettings);

        Log::debug('New event: Transaction Created', ['transactionId' => $aggregateRootId]);
    }

    /**
     * @param ChargeInformation $chargeInformation The charge information object
     * @return void
     */
    protected function setChargeInformation(?ChargeInformation $chargeInformation): void
    {
        if ($chargeInformation === null) {
            return; // no info, nothing to do
        }

        if ($chargeInformation->rebill() instanceof Rebill) {
            $this->rebillAmount    = (string) $chargeInformation->rebill()->amount();
            $this->rebillFrequency = (string) $chargeInformation->rebill()->frequency();
            $this->rebillStart     = (string) $chargeInformation->rebill()->start();
        }

        $this->amount   = (string) $chargeInformation->amount();
        $this->currency = (string) $chargeInformation->currency();
    }

    /**
     * @param PaymentInformation|null $paymentInformation Payment information object
     * @return void
     * @throws Exception
     */
    private function setCreditCardInformation(?PaymentInformation $paymentInformation): void
    {
        if ($paymentInformation === null) {
            return; // no info, nothing to do
        }

        if ($paymentInformation instanceof PaymentTemplateInformation) {
            $this->cardHash = (string) $paymentInformation->rocketGateCardHash();
            return;
        }

        if ($paymentInformation instanceof NetbillingPaymentTemplateInformation) {
            $this->cardHash = base64_encode($paymentInformation->netbillingCardHash()->value());
            return;
        }

        if ($paymentInformation instanceof CheckInformation) {
            Log::debug('There is no credit card information to be set on events from checks transactions.');
            return;
        }

        // if a payment template is not used we should store the following data
        $this->cvv2Check = $paymentInformation->cvv2Check() ? "true" : "false";
        $this->firstSix  = (string) $paymentInformation->creditCardNumber()->firstSix();
        $this->lastFour  = (string) $paymentInformation->creditCardNumber()->lastFour();

        $this->expirationYear  = (string) $paymentInformation->expirationYear();
        $this->expirationMonth = (string) $paymentInformation->expirationMonth();

        if ($paymentInformation->creditCardOwner() instanceof CreditCardOwner) {
            $this->ownerFirstName = $paymentInformation->creditCardOwner()->ownerFirstName();
            $this->ownerLastName  = $paymentInformation->creditCardOwner()->ownerLastName();
            $this->ownerEmail     = (string) $paymentInformation->creditCardOwner()->ownerEmail();
        }

        if ($paymentInformation->creditCardBillingAddress() instanceof CreditCardBillingAddress) {
            $this->ownerAddress = $paymentInformation->creditCardBillingAddress()->ownerAddress();
            $this->ownerCity    = $paymentInformation->creditCardBillingAddress()->ownerCity();
            $this->ownerCountry = $paymentInformation->creditCardBillingAddress()->ownerCountry();
            $this->ownerState   = $paymentInformation->creditCardBillingAddress()->ownerState();
            $this->ownerZip     = $paymentInformation->creditCardBillingAddress()->ownerZip();
            $this->ownerPhoneNo = $paymentInformation->creditCardBillingAddress()->ownerPhoneNo();
        }

        $this->creditCardNumber = self::OBFUSCATED_STRING;
        $this->cvv              = self::OBFUSCATED_STRING;
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function siteId(): string
    {
        return $this->siteId;
    }

    /**
     * @return string|null
     */
    public function cvv2Check(): ?string
    {
        return $this->cvv2Check;
    }

    /**
     * @return string|null
     */
    public function creditCardNumber(): ?string
    {
        return $this->creditCardNumber;
    }

    /**
     * @return string|null
     */
    public function ownerFirstName(): ?string
    {
        return $this->ownerFirstName;
    }

    /**
     * @return string|null
     */
    public function ownerLastName(): ?string
    {
        return $this->ownerLastName;
    }

    /**
     * @return string|null
     */
    public function ownerEmail(): ?string
    {
        return $this->ownerEmail;
    }

    /**
     * @return string|null
     */
    public function ownerAddress(): ?string
    {
        return $this->ownerAddress;
    }

    /**
     * @return string|null
     */
    public function ownerCity(): ?string
    {
        return $this->ownerCity;
    }

    /**
     * @return string|null
     */
    public function ownerCountry(): ?string
    {
        return $this->ownerCountry;
    }

    /**
     * @return string|null
     */
    public function ownerState(): ?string
    {
        return $this->ownerState;
    }

    /**
     * @return string|null
     */
    public function ownerZip(): ?string
    {
        return $this->ownerZip;
    }

    /**
     * @return string|null
     */
    public function ownerPhoneNo(): ?string
    {
        return $this->ownerPhoneNo;
    }

    /**
     * @return string|null
     */
    public function cvv(): ?string
    {
        return $this->cvv;
    }

    /**
     * @return string|null
     */
    public function expirationMonth(): ?string
    {
        return $this->expirationMonth;
    }

    /**
     * @return string|null
     */
    public function expirationYear(): ?string
    {
        return $this->expirationYear;
    }

    /**
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return string|null
     */
    public function rebillFrequency(): ?string
    {
        return $this->rebillFrequency;
    }

    /**
     * @return string|null
     */
    public function rebillStart(): ?string
    {
        return $this->rebillStart;
    }

    /**
     * @return string|null
     */
    public function rebillAmount(): ?string
    {
        return $this->rebillAmount;
    }

    /**
     * @return string
     */
    public function amount(): string
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function firstSix(): ?string
    {
        return $this->firstSix;
    }

    /**
     * @return string
     */
    public function cardHash(): string
    {
        return $this->cardHash;
    }

    /**
     * @return string|null
     */
    public function lastFour(): ?string
    {
        return $this->lastFour;
    }

    /**
     * @return string
     */
    public function paymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @return string
     */
    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
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
     * @return array
     */
    public function billerSettings(): array
    {
        return $this->billerSettings;
    }

    /**
     * @return string|null
     */
    public function previousTransactionId(): ?string
    {
        return $this->previousTransactionId;
    }

    /**
     * @return int|null
     */
    public function threedsVersion(): ?int
    {
        return $this->threedsVersion;
    }

    /**
     * @param BillerSettings $billerChargeSettings Biller Specific settings
     * @return void
     * @throws Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined
     */
    protected function setBillerSettings(?BillerSettings $billerChargeSettings): void
    {
        $this->billerSettings = $billerChargeSettings !== null ? $billerChargeSettings->toArray() : null;

        // Obfuscating if necessary
        if ($billerChargeSettings instanceof ObfuscatedData) {
            /** @var BillerSettingsObfuscator $obfuscator */
            $obfuscator           = BillerSettingsObfuscatorFactory::factory($billerChargeSettings->billerName());
            $this->billerSettings = $obfuscator->obfuscate($this->billerSettings);
        }
    }
}
