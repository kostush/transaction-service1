<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PerformRocketgateSaleCommand extends Command
{
    /**
     * @var string
     */
    private $siteId;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Rebill|null
     */
    private $rebill;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var BillerSettings
     */
    private $billerFields;

    /**
     * @var bool
     */
    private $useThreeD;

    /**
     * @var
     */
    private $isNSFSupported;

    /**
     * @var ?string
     */
    protected $returnUrl;

    /**
     * PerformRocketgateSaleCommand constructor.
     *
     * @param string|null    $siteId         The site id
     * @param float          $amount         The purchase amount
     * @param string|null    $currency       The purchase currency code
     * @param Payment        $payment        The payment parameters
     * @param BillerSettings $billerFields   The biller fields
     * @param Rebill|null    $rebill         The rebill fields
     * @param bool           $useThreeD      If 3D secure is triggered
     * @param string|null    $returnUrl      The return url
     * @param bool           $isNSFSupported If NSF is supported
     *
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     */
    public function __construct(
        ?string $siteId,
        $amount,
        ?string $currency,
        Payment $payment,
        BillerSettings $billerFields,
        ?Rebill $rebill,
        $useThreeD = false,
        ?string $returnUrl = null,
        $isNSFSupported = false
    ) {
        $this->initSiteId($siteId);
        $this->initCurrency($currency);
        $this->initAmount($amount);
        $this->initUseThreeD($useThreeD);
        $this->isNSFSupported = $isNSFSupported;
        $this->rebill         = $rebill;
        $this->payment        = $payment;
        $this->billerFields   = $billerFields;
        $this->returnUrl      = $returnUrl;
    }

    /**
     * @return string
     */
    public function returnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * @param null|string $siteId Site Id
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initSiteId(?string $siteId): void
    {
        if (empty($siteId)) {
            throw new MissingChargeInformationException('siteId');
        }

        $this->siteId = $siteId;
    }

    /**
     * @param mixed $amount Amount
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     * @throws InvalidChargeInformationException
     */
    private function initAmount($amount): void
    {
        if (is_null($amount)) {
            throw new MissingChargeInformationException('amount');
        }

        if (!$this->isValidFloat($amount)) {
            throw new InvalidChargeInformationException('amount');
        }

        $this->amount = (float) $amount;
    }

    /**
     * @param null|string $currency Currency
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initCurrency(?string $currency): void
    {
        if (empty($currency)) {
            throw new MissingChargeInformationException('currency');
        }

        $this->currency = $currency;
    }

    /**
     * @param bool $useThreeD Use ThreeD
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     */
    private function initUseThreeD($useThreeD): void
    {
        if (!is_bool($useThreeD)) {
            throw new InvalidChargeInformationException('useThreeD');
        }

        $this->useThreeD = $useThreeD;
    }

    /**
     * @return Payment
     */
    public function payment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return BillerSettings
     */
    public function billerFields(): BillerSettings
    {
        return $this->billerFields;
    }

    /**
     * @return Rebill
     */
    public function rebill(): ?Rebill
    {
        return $this->rebill;
    }

    /**
     * @return string|null
     */
    public function siteId(): ?string
    {
        return $this->siteId;
    }

    /**
     * @return float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function currency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function useThreeD(): bool
    {
        return $this->useThreeD;
    }

    /**
     * @return bool
     */
    public function isNSFSupported(): bool
    {
        return $this->isNSFSupported;
    }
}
