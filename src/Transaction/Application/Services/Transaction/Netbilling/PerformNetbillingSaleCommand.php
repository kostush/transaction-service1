<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidInitialDaysWithRebillInfoException;
use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PerformNetbillingSaleCommand extends Command
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
     * @var BillerLoginInfo
     */
    private $billerLoginInfo;

    /**
     * PerformNetbillingSaleCommand constructor.
     *
     * @param string|null          $siteId          The site id
     * @param float|null           $amount          The purchase amount
     * @param string|null          $currency        The purchase currency code
     * @param Payment              $payment         The payment parameters
     * @param BillerSettings       $billerFields    The biller fields
     * @param Rebill|null          $rebill          The rebill fields
     * @param BillerLoginInfo|null $billerLoginInfo The login information
     *
     * @throws InvalidChargeInformationException
     * @throws InvalidPaymentInformationException
     * @throws MissingChargeInformationException|InvalidInitialDaysWithRebillInfoException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        ?string $siteId,
        $amount,
        ?string $currency,
        Payment $payment,
        BillerSettings $billerFields,
        ?Rebill $rebill,
        ?BillerLoginInfo $billerLoginInfo = null
    ) {
        $this->initSiteId($siteId);
        $this->initCurrency($currency);
        $this->initAmount($amount);

        $this->validateChargeOnly($billerFields, $rebill);

        $this->rebill = $rebill;
        $this->initPaymentInfo($payment);
        $this->billerFields    = $billerFields;
        $this->billerLoginInfo = $billerLoginInfo;
    }

    /**
     * @param Payment $payment Payment infomation
     * @return void
     * @throws InvalidPaymentInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initPaymentInfo(Payment $payment)
    {
        if (empty($payment->information->member)
            && !($payment->information instanceof ExistingCreditCardInformation)
        ) {
            throw new InvalidPaymentInformationException();
        }

        $this->payment = $payment;
    }

    /**
     * @param null|string $siteId Site Id
     *
     * @return void
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initSiteId(?string $siteId)
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
     * @throws MissingChargeInformationException
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initAmount($amount)
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
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initCurrency(?string $currency)
    {
        if (empty($currency)) {
            throw new MissingChargeInformationException('currency');
        }

        $this->currency = $currency;
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
     * @return BillerLoginInfo|null
     */
    public function billerLoginInfo(): ?BillerLoginInfo
    {
        return $this->billerLoginInfo;
    }

    /**
     * @param BillerSettings $billerFields Biller Fields
     * @param Rebill|null    $rebill       Rebill
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidInitialDaysWithRebillInfoException
     */
    public function validateChargeOnly(BillerSettings $billerFields, ?Rebill $rebill): void
    {
        if (!empty($rebill) && $billerFields->initialDays() == 0) {
            throw new InvalidInitialDaysWithRebillInfoException();
        }
    }
}
