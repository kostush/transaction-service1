<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Netbilling;

use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;

abstract class NetbillingBillerSettings implements BillerSettings
{
    /**
     * NetbillingBillerSettings constructor.
     * @param string $siteTag   site tag
     * @param string $accountId account id
     */
    public function __construct(string $siteTag, string $accountId)
    {
        $this->siteTag   = $siteTag;
        $this->accountId = $accountId;
    }

    /**
     * @var string
     */
    protected $siteTag;

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @return string
     */
    public function siteTag(): string
    {
        return $this->siteTag;
    }

    /**
     * @param string|null $siteTag site Tag
     *
     * @return void
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function initSiteTag(string $siteTag) :void
    {
        if (empty($siteTag)) {
            throw new MissingMerchantInformationException('siteTag');
        }

        $this->siteTag = $siteTag;
    }

    /**
     * @return string
     */
    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @param string|null $accountId account ID
     *
     * @return void
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function initAccountId(?string $accountId) :void
    {
        if (empty($accountId)) {
            throw new MissingMerchantInformationException('accountId');
        }

        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return self::NETBILLING;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'siteTag'   => $this->siteTag,
            'accountId' => $this->accountId
        ];
    }
}
