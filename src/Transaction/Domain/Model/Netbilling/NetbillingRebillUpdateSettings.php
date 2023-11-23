<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Netbilling;

use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;

class NetbillingRebillUpdateSettings extends NetbillingBillerSettings
{
    /**
     * @var string
     */
    protected $billerMemberId;

    /**
     * @var string
     */
    protected $merchantPassword;

    /**
     * @var int
     */
    protected $initialDays;

    /**
     * @var string|null
     */
    protected $binRouting;

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
     * NetbillingRebillUpdateSettings constructor.
     * @param string      $siteTag
     * @param string      $accountId
     * @param string      $billerMemberId
     * @param string|null $merchantPassword
     * @param int         $initialDays
     * @param string|null $ipAddress
     * @param string|null $browser
     * @param string|null $host
     * @param string|null $description
     * @param string|null $binRouting
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function __construct(
        string $siteTag,
        string $accountId,
        string $billerMemberId,
        string $merchantPassword,
        ?int $initialDays,
        ?string $ipAddress,
        ?string $browser,
        ?string $host,
        ?string $description,
        ?string $binRouting
    ) {
        $this->initSiteTag($siteTag);
        $this->initAccountId($accountId);
        $this->billerMemberId = $billerMemberId;
        $this->initialDays    = $initialDays;
        $this->binRouting     = $binRouting;
        $this->ipAddress      = $ipAddress;
        $this->description    = $description;
        $this->browser        = $browser;
        $this->host           = $host;
        $this->initMerchantPassword($merchantPassword);
    }

    public static function create(
        string $siteTag,
        string $accountId,
        string $billerMemberId,
        string $merchantPassword,
        ?int $initialDays = null,
        ?string $binRouting = null,
        ?string $ipAddress = null,
        ?string $browser = null,
        ?string $host = null,
        ?string $description = null
    ): self {
        return new static(
            $siteTag,
            $accountId,
            $billerMemberId,
            $merchantPassword,
            $initialDays,
            $ipAddress,
            $browser,
            $host,
            $description,
            $binRouting
        );
    }

    /**
     * @return string|null
     */
    public function billerMemberId(): string
    {
        return $this->billerMemberId;
    }

    /**
     * @return string
     */
    public function merchantPassword(): string
    {
        return $this->merchantPassword;
    }

    /**
     * @return int
     */
    public function initialDays(): int
    {
        return $this->initialDays;
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
    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $merchantPassword
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function initMerchantPassword(string $merchantPassword) :void
    {
        if (empty($merchantPassword)) {
            throw new MissingMerchantInformationException('merchantPassword');
        }

        $this->merchantPassword = $merchantPassword;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'billerMemberId'   => $this->billerMemberId(),
                'merchantPassword' => $this->merchantPassword(),
                'initialDays'      => $this->initialDays,
                'host'             => $this->host(),
                'browser'          => $this->browser(),
                'ipAddress'        => $this->ipAddress(),
                'binRouting'       => $this->binRouting()
            ]
        );
    }
}