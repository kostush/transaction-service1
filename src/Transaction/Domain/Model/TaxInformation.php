<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;

class TaxInformation
{
    /**
     * @var string|null
     */
    private $taxName;

    /**
     * @var float|null
     */
    private $taxRate;

    /**
     * @var string|null
     */
    private $taxApplicationId;

    /**
     * @var string|null
     */
    private $taxCustom;

    /**
     * @var string
     */
    private $taxType;

    /**
     * @var TaxAmount|null
     */
    private $initialAmount;

    /**
     * @var TaxAmount|null
     */
    private $rebillAmount;

    /**
     * @var bool|null
     */
    private $displayChargedAmount;

    /**
     * TaxInformation constructor.
     * @param string|null    $taxName              The tax name object
     * @param float|null     $taxRate              The tax rate object
     * @param string|null    $taxApplicationId     The tax application id
     * @param string|null    $taxCustom            Custom information for tax
     * @param string|null    $taxType              Tax Type.
     * @param TaxAmount|null $initialAmount        Inital Amount
     * @param TaxAmount|null $rebillAmount         Rebill Amount
     * @param bool|null      $displayChargedAmount Display Charge Amount
     */
    private function __construct(
        ?string $taxName,
        ?float $taxRate,
        ?string $taxApplicationId,
        ?string $taxCustom,
        ?string $taxType,
        ?TaxAmount $initialAmount,
        ?TaxAmount $rebillAmount,
        ?bool $displayChargedAmount
    ) {
        $this->taxName              = $taxName;
        $this->taxRate              = $taxRate;
        $this->taxApplicationId     = $taxApplicationId;
        $this->taxCustom            = $taxCustom;
        $this->taxType              = $taxType;
        $this->initialAmount        = $initialAmount;
        $this->rebillAmount         = $rebillAmount;
        $this->displayChargedAmount = $displayChargedAmount;
    }

    /**
     * @param array $arrayTaxInformation Array tax Information
     * @return TaxInformation
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    public static function createFromArray(array $arrayTaxInformation): self
    {
        return new static(
            $arrayTaxInformation['taxName'] ?? null,
            $arrayTaxInformation['taxRate'] ?? null,
            $arrayTaxInformation['taxApplicationId'] ?? null,
            $arrayTaxInformation['taxCustom'] ?? null,
            $arrayTaxInformation['taxType'] ?? null,
            (!empty($arrayTaxInformation['initialAmount']) ?
                TaxAmount::createFromArray($arrayTaxInformation['initialAmount'])
                : null),
            (!empty($arrayTaxInformation['rebillAmount']) ?
                TaxAmount::createFromArray($arrayTaxInformation['rebillAmount'])
                : null),
            $arrayTaxInformation['displayChargedAmount'] ?? null
        );
    }

    /**
     * @return string|null
     */
    public function taxName(): ?string
    {
        return $this->taxName;
    }

    /**
     * @return float|null
     */
    public function taxRate(): ?float
    {
        return $this->taxRate;
    }

    /**
     * @return string|null
     */
    public function taxApplicationId(): ?string
    {
        return $this->taxApplicationId;
    }

    /**
     * @return string|null
     */
    public function taxCustom(): ?string
    {
        return $this->taxCustom;
    }

    /**
     * @return string|null
     */
    public function taxType(): ?string
    {
        return $this->taxType;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $taxInfo = [];

        if (!empty($this->taxApplicationId())) {
            $taxInfo['taxApplicationId'] = (string) $this->taxApplicationId();
        }

        if (!empty($this->taxName())) {
            $taxInfo['taxName'] = (string) $this->taxName();
        }

        if (!empty($this->taxRate())) {
            $taxInfo['taxRate'] = $this->taxRate();
        }

        if (!empty($this->taxCustom())) {
            $taxInfo['custom'] = (string) $this->taxCustom();
        }

        if (!empty($this->taxType())) {
            $taxInfo['taxType'] = (string) $this->taxType();
        }

        if (!empty($this->initialAmount())) {
            $taxInfo['initialAmount'] = $this->initialAmount()->toArray();
        }

        if (!empty($this->rebillAmount())) {
            $taxInfo['rebillAmount'] = $this->rebillAmount()->toArray();
        }

        return $taxInfo;
    }

    /**
     * @return TaxAmount|null
     */
    public function initialAmount(): ?TaxAmount
    {
        return $this->initialAmount;
    }

    /**
     * @return TaxAmount|null
     */
    public function rebillAmount(): ?TaxAmount
    {
        return $this->rebillAmount;
    }

    /**
     * @return bool|null
     */
    public function displayChargedAmount(): ?bool
    {
        return $this->displayChargedAmount;
    }

    /**
     * @param Amount|null $amount Amount
     * @param Rebill|null $rebill Rebill Amount
     * @return void
     * @throws Exception
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function validateAfterTaxAmount(?Amount $amount, ?Rebill $rebill): void
    {
        if (!empty($amount) && !empty($this->initialAmount())) {
            $this->initialAmount->validateAfterTax($amount);
        }
        if (!empty($rebill) && !empty($this->rebillAmount())) {
            $this->rebillAmount->validateRebillAfterTax($rebill->amount());
        }
    }
}
