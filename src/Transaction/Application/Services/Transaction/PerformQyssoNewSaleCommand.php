<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PerformQyssoNewSaleCommand extends Command
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $siteId;

    /**
     * @var string
     */
    private $siteName;

    /**
     * @var string
     */
    private $clientIp;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var array
     */
    private $payment;

    /**
     * @var array
     */
    private $tax;

    /**
     * @var BillerSettings
     */
    private $billerFields;

    /** @var Member */
    private $member;

    /**
     * @var Rebill|null
     */
    private $rebill;

    /**
     * @param string         $sessionId    Session Id
     * @param string         $siteId       Site Id
     * @param string         $siteName     Site Name
     * @param string         $clientIp     Client IP
     * @param float          $amount       Amount
     * @param string         $currency     Currency
     * @param array          $payment      Payment
     * @param array          $tax          Tax
     * @param BillerSettings $billerFields Biller Fields
     * @param Member|null    $member       Member
     * @param Rebill|null    $rebill       Rebill
     *
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $sessionId,
        string $siteId,
        string $siteName,
        string $clientIp,
        float $amount,
        string $currency,
        array $payment,
        array $tax,
        BillerSettings $billerFields,
        ?Member $member,
        ?Rebill $rebill
    ) {
        $this->sessionId = $sessionId;
        $this->initSiteId($siteId);
        $this->initSiteName($siteName);
        $this->initClientIp($clientIp);
        $this->initAmount($amount);
        $this->initCurrency($currency);
        $this->initPayment($payment);
        $this->tax          = $tax;
        $this->billerFields = $billerFields;
        $this->member       = $member;
        $this->rebill       = $rebill;
    }

    /**
     * @param string $siteId Site Id
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initSiteId(string $siteId): void
    {
        if (empty($siteId)) {
            throw new MissingChargeInformationException('siteId');
        }

        $this->siteId = $siteId;
    }

    /**
     * @param string $siteName Site Name
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initSiteName(string $siteName): void
    {
        if (empty($siteName)) {
            throw new MissingChargeInformationException('siteName');
        }

        $this->siteName = $siteName;
    }

    /**
     * @param string $clientIp Client IP
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initClientIp(string $clientIp): void
    {
        if (empty($clientIp)) {
            throw new MissingChargeInformationException('clientIp');
        }

        $this->clientIp = $clientIp;
    }

    /**
     * @param float $amount Amount
     *
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initAmount(float $amount): void
    {
        if ($amount < 0 || !$this->isValidFloat($amount)) {
            throw new InvalidChargeInformationException('amount');
        }

        $this->amount = $amount;
    }

    /**
     * @param string $currency Currency
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initCurrency(string $currency): void
    {
        if (empty($currency)) {
            throw new MissingChargeInformationException('currency');
        }

        $this->currency = $currency;
    }

    /**
     * @param array $payment Payment
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initPayment(array $payment): void
    {
        if (empty($payment)) {
            throw new MissingChargeInformationException('paymentType');
        }

        $this->payment = $payment;
    }

    /**
     * @return string
     */
    public function sessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @return string
     */
    public function siteId(): string
    {
        return $this->siteId;
    }

    /**
     * @return string
     */
    public function siteName(): string
    {
        return $this->siteName;
    }

    /**
     * @return string
     */
    public function clientIp(): string
    {
        return $this->clientIp;
    }

    /**
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * @return Rebill
     */
    public function rebill(): ?Rebill
    {
        return $this->rebill;
    }

    /**
     * @return string
     */
    public function paymentType(): string
    {
        return $this->payment['type'] ?? '';
    }

    /**
     * @return string
     */
    public function paymentMethod(): string
    {
        return $this->payment['method'] ?? '';
    }

    /**
     * @return string|null
     */
    public function username(): ?string
    {
        return $this->member()['userName'] ?? null;
    }

    /**
     * @return Member|null
     */
    public function member(): ?Member
    {
        return $this->member;
    }

    /**
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->member()['password'] ?? null;
    }

    /**
     * @return array
     */
    public function tax(): array
    {
        return $this->tax;
    }

    /**
     * @return array
     */
    public function payment(): array
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
}
