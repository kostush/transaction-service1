<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Netbilling;

use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;

class NetbillingChargeSettings extends NetbillingBillerSettings
{
    use Validators;

    /**
     * @var int
     */
    private $initialDays;

    /**
     * @var string
     */
    protected $siteTag;

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string|null
     */
    private $merchantPassword;

    /**
     * @var string|null
     */
    protected $ipAddress;

    /**
     * @var string|null
     */
    protected $browser;

    /**
     * @var string|null
     */
    protected $host;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $binRouting;

    /**
     * @var string|null
     */
    protected $billerMemberId;

    /** @var bool */
    protected $disableFraudChecks;

    /**
     * NetbillingChargeSettings constructor.
     * @param string|null $siteTag            Site Tag
     * @param string|null $accountId          Account ID
     * @param string|null $merchantPassword   Merchant Password
     * @param int|null    $initialDays        Duration
     * @param string|null $ipAddress          Ip address
     * @param string|null $browser            Clients Browser
     * @param string|null $host               Host of client
     * @param string|null $description        description of bundle
     * @param string|null $binRouting         Bin routing code
     * @param string|null $billerMemberId     Biller Member Id
     * @param boolean     $disableFraudChecks disable Fraud Checks flag to send Netbilling
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    public function __construct(
        ?string $siteTag,
        ?string $accountId,
        ?string $merchantPassword,
        ?int $initialDays,
        ?string $ipAddress,
        ?string $browser,
        ?string $host,
        ?string $description,
        ?string $binRouting,
        ?string $billerMemberId,
        bool $disableFraudChecks = false
    ) {
        $this->initSiteTag($siteTag);
        $this->initAccountId($accountId);
        $this->initInitialDays($initialDays);
        $this->initIpAddress($ipAddress);

        $this->initMerchantPassword($merchantPassword);

        $this->browser            = $browser;
        $this->host               = $host;
        $this->description        = $description;
        $this->binRouting         = $binRouting;
        $this->billerMemberId     = $billerMemberId;
        $this->disableFraudChecks = $disableFraudChecks;
    }

    /**
     * @param string|null $siteTag            Site Tag
     * @param string|null $accountId          Account ID
     * @param string|null $merchantPassword   Merchant Password
     * @param int|null    $initialDays        Duration
     * @param string|null $ipAddress          Ip address
     * @param string|null $browser            Clients Browser
     * @param string|null $host               Host of client
     * @param string|null $description        description of bundle
     * @param string|null $binRouting         Bin routing code
     * @param string|null $billerMemberId     Biller Member Id
     * @param boolean     $disableFraudChecks disable Fraud Checks flag
     * @return NetbillingChargeSettings
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    public static function create(
        ?string $siteTag,
        ?string $accountId,
        ?string $merchantPassword,
        ?int $initialDays,
        ?string $ipAddress,
        ?string $browser,
        ?string $host,
        ?string $description,
        ?string $binRouting,
        ?string $billerMemberId = null,
        bool $disableFraudChecks = false
    ): self {
        return new static(
            $siteTag,
            $accountId,
            $merchantPassword,
            $initialDays,
            $ipAddress,
            $browser,
            $host,
            $description,
            $binRouting,
            $billerMemberId,
            $disableFraudChecks
        );
    }

    /**
     * Compare two Netbilling Charge Settings objects
     *
     * @param NetbillingChargeSettings $netbillingChargeSettings Charge Settings
     *
     * @return bool
     */
    public function equals(NetbillingChargeSettings $netbillingChargeSettings): bool
    {
        return (
            $this->siteTag() === $netbillingChargeSettings->siteTag()
            && $this->accountId() === $netbillingChargeSettings->accountId()
            && $this->merchantPassword() === $netbillingChargeSettings->merchantPassword()
            && $this->initialDays() === $netbillingChargeSettings->initialDays()
            && $this->ipAddress() === $netbillingChargeSettings->ipAddress()
            && $this->browser() === $netbillingChargeSettings->browser()
            && $this->host() === $netbillingChargeSettings->host()
            && $this->description() === $netbillingChargeSettings->description()
            && $this->binRouting() === $netbillingChargeSettings->binRouting()
            && $this->billerMemberId() === $netbillingChargeSettings->billerMemberId()
            && $this->disableFraudChecks() === $netbillingChargeSettings->disableFraudChecks()
        );
    }

    /**
     * @param string|null $merchantPassword merchant password
     *
     * @return void
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    protected function initMerchantPassword(?string $merchantPassword): void
    {
        if (empty($merchantPassword) || is_null($merchantPassword)) {
            throw new MissingMerchantInformationException('merchantPassword');
        }

        $this->merchantPassword = $merchantPassword;
    }

    /**
     * @param int $initialDays Initial duration
     *
     * @return void
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws LoggerException
     */
    private function initInitialDays($initialDays)
    {
        if (is_null($initialDays)) {
            throw new MissingInitialDaysException('initialDays');
        }

        if (!$this->isValidInteger($initialDays)) {
            throw new InvalidPayloadException();
        }

        $this->initialDays = $initialDays;
    }

    /**
     * @return int
     */
    public function initialDays(): int
    {
        return (int) $this->initialDays;
    }

    /**
     * Validate and return ip address
     *
     * @param string|null $ipAddress Ip Address
     *
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     */
    private function initIpAddress(?string $ipAddress): void
    {
        if (!empty($ipAddress) && !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidMerchantInformationException('ipAddress');
        }

        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string
     */
    public function siteTag(): string
    {
        return $this->siteTag;
    }

    /**
     * @return string
     */
    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function merchantPassword(): ?string
    {
        return $this->merchantPassword;
    }

    /**
     * @return string|null
     */
    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @return string|null
     */
    public function browser(): ?string
    {
        return $this->browser;
    }

    /**
     * @return string|null
     */
    public function host(): ?string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function binRouting(): ?string
    {
        return $this->binRouting;
    }

    /**
     * @return string|null
     */
    public function billerMemberId(): ?string
    {
        return $this->billerMemberId;
    }

    /**
     * @return bool
     */
    public function disableFraudChecks(): bool
    {
        return $this->disableFraudChecks;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'merchantPassword'   => $this->merchantPassword(),
                'initialDays'        => $this->initialDays(),
                'host'               => $this->host(),
                'browser'            => $this->browser(),
                'ipAddress'          => $this->ipAddress(),
                'binRouting'         => $this->binRouting(),
                'billerMemberId'     => $this->billerMemberId(),
                'disableFraudChecks' => $this->disableFraudChecks()
            ]
        );
    }
}
