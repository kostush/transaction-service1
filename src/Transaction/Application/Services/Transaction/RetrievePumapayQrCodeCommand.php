<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class RetrievePumapayQrCodeCommand extends Command
{
    /**
     * @var string
     */
    private $siteId;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var Rebill
     */
    private $rebill;

    /**
     * @var string
     */
    private $businessId;

    /**
     * @var string
     */
    private $businessModel;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * RetrieveQrCodeCommand constructor.
     * @param string      $siteId        Site Id
     * @param string      $currency      Currency
     * @param float       $amount        Amount
     * @param Rebill      $rebill        Rebill
     * @param string      $businessId    Business Id
     * @param string      $businessModel Business Model
     * @param string      $apiKey        Api key
     * @param string      $title         Title
     * @param string      $description   Description
     * @param string|null $transactionId TransactionId
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $siteId,
        string $currency,
        float $amount,
        ?Rebill $rebill,
        string $businessId,
        string $businessModel,
        string $apiKey,
        string $title,
        string $description,
        ?string $transactionId = null
    ) {
        $this->initSiteId($siteId);
        $this->initCurrency($currency);
        $this->initAmount($amount);
        $this->initBusinessId($businessId);
        $this->initBusinessModel($businessModel);
        $this->initApiKey($apiKey);
        $this->initTitle($title);
        $this->initDescription($description);
        $this->rebill        = $rebill;
        $this->transactionId = $transactionId;
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
    public function businessId(): string
    {
        return $this->businessId;
    }

    /**
     * @return string
     */
    public function businessModel(): string
    {
        return $this->businessModel;
    }

    /**
     * @return string
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @param string $siteId Site Id
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
     * @param string $currency Currency
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
     * @param float $amount
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
     * @param string $businessId Business Id
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initBusinessId(string $businessId): void
    {
        if (empty($businessId)) {
            throw new MissingChargeInformationException('businessId');
        }

        $this->businessId = $businessId;
    }

    /**
     * @param string $businessModel Business model
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initBusinessModel(string $businessModel): void
    {
        if (empty($businessModel)) {
            throw new MissingChargeInformationException('businessModel');
        }

        $this->businessModel = $businessModel;
    }

    /**
     * @param string $apiKey Api key
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initApiKey(string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new MissingChargeInformationException('apiKey');
        }

        $this->apiKey = $apiKey;
    }

    /**
     * @param string $title Title
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initTitle(string $title): void
    {
        if (empty($title)) {
            throw new MissingChargeInformationException('title');
        }

        $this->title = $title;
    }

    /**
     * @param string $description Description
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initDescription(string $description): void
    {
        if (empty($description)) {
            throw new MissingChargeInformationException('description');
        }

        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function transactionId(): ?string
    {
        return $this->transactionId;
    }
}
