<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;

class Charge
{
    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var string
     */
    private $siteId;

    /**
     * @var bool
     */
    private $isMainPurchase;

    /**
     * @var bool|null
     */
    private $preChecked;

    /**
     * @var TaxInformation|null
     */
    private $taxInformation;

    /**
     * @var Rebill|null
     */
    private $rebill;

    /**
     * Charge constructor.
     * @param Amount|null $amount         Amount
     * @param Currency    $currency       Currency
     * @param int         $productId      Product Id
     * @param string      $siteId         Site Id
     * @param bool        $isMainPurchase Is main Purchase
     * @param bool|null   $preChecked     Pre checked
     * @param array|null  $rebill         Rebill
     * @param array|null  $taxInformation Tax Information
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function __construct(
        ?Amount $amount,
        Currency $currency,
        int $productId,
        string $siteId,
        bool $isMainPurchase,
        ?bool $preChecked,
        ?array $rebill,
        ?array $taxInformation
    ) {
        $this->amount         = $amount;
        $this->currency       = $currency;
        $this->productId      = $productId;
        $this->siteId         = $siteId;
        $this->isMainPurchase = $isMainPurchase;
        $this->preChecked     = $preChecked;
        $this->rebill         = $this->returnRebill($rebill);
        $this->taxInformation = $this->returnTaxInformation($taxInformation);
    }

    /**
     * @param string     $currency       Currency
     * @param int        $productId      ProductId
     * @param string     $siteId         SiteId
     * @param bool       $isMainPurchase Is Main Purchase
     * @param float|null $amount         Amount
     * @param bool|null  $preChecked     Prechecked
     * @param array|null $rebill         Rebill
     * @param array|null $tax            Tax
     * @return Charge
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public static function create(
        string $currency,
        int $productId,
        string $siteId,
        bool $isMainPurchase,
        ?float $amount,
        ?bool $preChecked,
        ?array $rebill,
        ?array $tax
    ): self {
        return new static(
            Amount::create($amount),
            Currency::create($currency),
            $productId,
            $siteId,
            $isMainPurchase,
            $preChecked,
            $rebill,
            $tax
        );
    }

    /**
     * @param array $rebill Rebill
     * @return Rebill|null
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    private function returnRebill(?array $rebill): ?Rebill
    {
        if (empty($rebill)) {
            return null;
        }

        return Rebill::create(
            $rebill['frequency'] ?? null,
            $rebill['start'] ?? null,
            Amount::create((float) $rebill['amount'])
        );
    }

    /**
     * @param array|null $tax Tax
     * @return TaxInformation|null
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    private function returnTaxInformation(?array $tax): ?TaxInformation
    {
        if (empty($tax)) {
            return null;
        }

        $taxInformation = TaxInformation::createFromArray($tax);
        $taxInformation->validateAfterTaxAmount($this->amount(), $this->rebill());
        return $taxInformation;
    }

    /**
     * @return Amount|null
     */
    public function amount(): ?Amount
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function currency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function productId(): int
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function siteId(): string
    {
        return $this->siteId;
    }

    /**
     * @return bool
     */
    public function isMainPurchase(): bool
    {
        return $this->isMainPurchase;
    }

    /**
     * @return bool|null
     */
    public function preChecked(): ?bool
    {
        return $this->preChecked;
    }

    /**
     * @return TaxInformation|null
     */
    public function taxInformation(): ?TaxInformation
    {
        return $this->taxInformation;
    }

    /**
     * @return Rebill|null
     */
    public function rebill(): ?Rebill
    {
        return $this->rebill;
    }

    /**
     * to be pity with ChargesCollection class
     * That has a toArray method that expects its objects
     * have a toArray method.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount'         => $this->amount()->value(),
            'currency'       => (string) $this->currency,
            'productId'      => $this->productId(),
            'siteId'         => $this->siteId(),
            'isMainPurchase' => $this->isMainPurchase(),
            'preChecked'     => $this->preChecked(),
            'tax'            => $this->taxInformation(),
            'rebill'         => (!empty($this->rebill()) ? $this->rebill() : null)
        ];
    }
}
