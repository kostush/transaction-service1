<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use Exception;
use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use Ramsey\Uuid\Uuid;

class RocketGateChargeSettings extends RocketGateBillerSettings implements ObfuscatedData
{
    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string|null
     */
    protected $merchantId;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string|null
     */
    protected $merchantPassword;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string|null
     */
    protected $merchantCustomerId;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string|null
     */
    protected $merchantInvoiceId;

    /**
     * @var string|null
     */
    protected $merchantAccount;

    /**
     * @var string|null
     */
    protected $ipAddress;

    /**
     * @var string|null
     */
    protected $merchantSiteId;

    /**
     * @var string|null
     */
    protected $sharedSecret;

    /**
     * @var bool|null
     */
    protected $simplified3DS;

    /**
     * @var string|null
     */
    protected $merchantProductId;

    /**
     * @var string|null
     */
    protected $merchantDescriptor;

    /**
     * RocketGateChargeSettings constructor.
     *
     * @param string|null $merchantId          Merchant Id
     * @param string|null $merchantPassword    Merchant password
     * @param string|null $merchantCustomerId  Merchant customer Id
     * @param string|null $merchantInvoiceId   Merchant invoice Id
     * @param string|null $merchantAccount     Merchant Account
     * @param string|null $merchantSiteId      Merchant site Id
     * @param string|null $merchantProductId   Merchant product Id
     * @param string|null $merchantDescriptor  Merchant descriptor
     * @param string|null $ipAddress           Merchant ip address
     * @param string|null $referringMerchantId Referring merchant Id
     * @param string|null $sharedSecret        Shared secret
     * @param bool|null   $simplified3DS       Simplified 3DS flag
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws Exception
     */
    protected function __construct(
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $merchantCustomerId,
        ?string $merchantInvoiceId,
        ?string $merchantAccount,
        ?string $merchantSiteId,
        ?string $merchantProductId,
        ?string $merchantDescriptor,
        ?string $ipAddress,
        ?string $referringMerchantId,
        ?string $sharedSecret,
        ?bool $simplified3DS
    ) {
        $this->initMerchantId($merchantId);
        $this->checkMerchantPassword($merchantPassword);
        $this->initMerchantCustomerId($merchantCustomerId);
        $this->initMerchantInvoiceId($merchantInvoiceId);
        $this->initIpAddress($ipAddress);
        $this->sharedSecret        = $sharedSecret;
        $this->simplified3DS       = $simplified3DS;
        $this->merchantSiteId      = $merchantSiteId;
        $this->merchantAccount     = $merchantAccount;
        $this->merchantProductId   = $merchantProductId;
        $this->merchantDescriptor  = $merchantDescriptor;
        $this->referringMerchantId = $referringMerchantId;
    }

    /**
     * @param string|null $merchantId          Merchant Id
     * @param string|null $merchantPassword    Merchant password
     * @param string|null $merchantCustomerId  Merchant customer Id
     * @param string|null $merchantInvoiceId   Merchant invoice Id
     * @param string|null $merchantAccount     Merchant Account
     * @param string|null $merchantSiteId      Merchant site Id
     * @param string|null $merchantProductId   Merchant product Id
     * @param string|null $merchantDescriptor  Merchant descriptor
     * @param string|null $ipAddress           Merchant ip address
     * @param string|null $referringMerchantId Referring merchant Id
     * @param string|null $sharedSecret        Shared secret
     * @param bool|null   $simplified3DS       Simplified 3DS flag
     * @return RocketGateChargeSettings
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $merchantCustomerId,
        ?string $merchantInvoiceId,
        ?string $merchantAccount,
        ?string $merchantSiteId,
        ?string $merchantProductId,
        ?string $merchantDescriptor,
        ?string $ipAddress,
        ?string $referringMerchantId,
        ?string $sharedSecret,
        ?bool $simplified3DS
    ): self {
        return new static(
            $merchantId,
            $merchantPassword,
            $merchantCustomerId,
            $merchantInvoiceId,
            $merchantAccount,
            $merchantSiteId,
            $merchantProductId,
            $merchantDescriptor,
            $ipAddress,
            $referringMerchantId,
            $sharedSecret,
            $simplified3DS
        );
    }

    /**
     * @param string|null $merchantId Merchant Id
     * @return void
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initMerchantId(?string $merchantId): void
    {
        if (empty($merchantId)) {
            throw new MissingMerchantInformationException('merchantId');
        }

        $this->merchantId = $merchantId;
    }

    /**
     * Validate and return ip address
     *
     * @param string|null $ipAddress Ip Address
     *
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initIpAddress(?string $ipAddress): void
    {
        if (!empty($ipAddress) && !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidMerchantInformationException('ipAddress');
        }

        $this->ipAddress = $ipAddress;
    }

    /**
     * Get $merchantSiteId
     * @return string
     */
    public function merchantSiteId(): ?string
    {
        return $this->merchantSiteId;
    }

    /**
     * Get $merchantProductId
     * @return string
     */
    public function merchantProductId(): ?string
    {
        return $this->merchantProductId;
    }

    /**
     * Get $merchantDescriptor
     * @return string|null
     */
    public function merchantDescriptor(): ?string
    {
        return $this->merchantDescriptor;
    }

    /**
     * Get $merchantAccount
     * @return string
     */
    public function merchantAccount(): ?string
    {
        return $this->merchantAccount;
    }

    /**
     * Get $ipAddress
     * @return string
     */
    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @return string
     */
    public function sharedSecret(): ?string
    {
        return $this->sharedSecret;
    }

    /**
     * @return bool
     */
    public function simplified3DS(): ?bool
    {
        return (bool) $this->simplified3DS;
    }

    /**
     * Compare two Rocketgate Charge Settings objects
     *
     * @param RocketGateChargeSettings $rocketgateChargeSettings Charge Settings
     *
     * @return bool
     */
    public function equals(RocketGateChargeSettings $rocketgateChargeSettings): bool
    {
        return (
            $this->merchantId() === $rocketgateChargeSettings->merchantId()
            && $this->merchantPassword() === $rocketgateChargeSettings->merchantPassword()
            && $this->merchantCustomerId() === $rocketgateChargeSettings->merchantCustomerId()
            && $this->merchantInvoiceId() === $rocketgateChargeSettings->merchantInvoiceId()
            && $this->merchantAccount() === $rocketgateChargeSettings->merchantAccount()
            && $this->merchantSiteId() === $rocketgateChargeSettings->merchantSiteId()
            && $this->merchantProductId() === $rocketgateChargeSettings->merchantProductId()
            && $this->merchantDescriptor() === $rocketgateChargeSettings->merchantDescriptor()
            && $this->ipAddress() === $rocketgateChargeSettings->ipAddress()
            && $this->sharedSecret() === $rocketgateChargeSettings->sharedSecret()
            && $this->simplified3DS() === $rocketgateChargeSettings->simplified3DS()
        );
    }

    /**
     * @param string $merchantPassword the merchant password
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function checkMerchantPassword($merchantPassword): void
    {
        if (empty($merchantPassword)) {
            throw new MissingMerchantInformationException('merchantPassword');
        } elseif (!is_string($merchantPassword)) {
            throw new InvalidMerchantInformationException('merchantPassword');
        }

        $this->merchantPassword = $merchantPassword;
    }

    /**
     * @param null|string $merchantCustomerId Customer Id
     * @return void
     * @throws Exception
     */
    protected function initMerchantCustomerId(?string $merchantCustomerId): void
    {
        if (empty($merchantCustomerId)) {
            $this->merchantCustomerId = $this->generateUniqueId();
        } else {
            $this->merchantCustomerId = $merchantCustomerId;
        }
    }

    /**
     * @param null|string $merchantInvoiceId Invoice Id
     * @return void
     * @throws Exception
     */
    protected function initMerchantInvoiceId(?string $merchantInvoiceId): void
    {
        if (empty($merchantInvoiceId)) {
            $this->merchantInvoiceId = $this->generateUniqueId();
        } else {
            $this->merchantInvoiceId = $merchantInvoiceId;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function generateUniqueId(): string
    {
        // TODO Change to UUID when Legacy supports it
        return uniqid(substr(Uuid::uuid4()->toString(), 0, 9), true);
    }

    /**
     * @return string|null
     */
    public function binRouting(): ?string
    {
        return $this->merchantAccount;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                "merchantSiteId"     => $this->merchantSiteId(),
                "merchantProductId"  => $this->merchantProductId(),
                "merchantDescriptor" => $this->merchantDescriptor(),
                "merchantAccount"    => $this->merchantAccount(),
                "ipAddress"          => $this->ipAddress(),
                "sharedSecret"       => $this->sharedSecret(),
                "simplified3DS"      => $this->simplified3DS(),
            ]
        );
    }
}
