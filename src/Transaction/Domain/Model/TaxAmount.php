<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;

class TaxAmount
{
    public const INTIAL_TAX_AMOUNT = 'tax.initial amount.after taxes';
    public const REBILL_TAX_AMOUNT = 'tax.rebill amount.after taxes';
    public const AMOUNT            = 'amount';
    public const REBILL_AMOUNT     = 'rebill amount';

    /**
     * @var Amount
     */
    private $beforeTaxes;
    /**
     * @var Amount
     */
    private $taxes;
    /**
     * @var Amount
     */
    private $afterTaxes;

    /**
     * TaxAmount constructor.
     * @param Amount $beforeTaxes Before tax amount.
     * @param Amount $taxes       Taxes.
     * @param Amount $afterTaxes  After taxes amount.
     */
    private function __construct(Amount $beforeTaxes, Amount $taxes, Amount $afterTaxes)
    {
        $this->beforeTaxes = $beforeTaxes;
        $this->taxes       = $taxes;
        $this->afterTaxes  = $afterTaxes;
    }

    /**
     * @param float $beforeTaxes Before Tax
     * @param float $taxes       Tax
     * @param float $afterTaxes  After Tax
     * @return TaxAmount
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    public static function create(float $beforeTaxes, float $taxes, float $afterTaxes): self
    {
        return new self(Amount::create($beforeTaxes), Amount::create($taxes), Amount::create($afterTaxes));
    }

    /**
     * @param array $array Array object.
     * @return TaxAmount|static
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    public static function createFromArray(array $array)
    {
        self::assertRequiredKeysExists(['beforeTaxes', 'taxes', 'afterTaxes'], $array);

        return TaxAmount::create(
            (float) $array['beforeTaxes'],
            (float) $array['taxes'],
            (float) $array['afterTaxes']
        );
    }

    /**
     * @param array $keys  Keys that should be on array.
     * @param array $array Keys to validate.
     * @throws Exception
     * @return void
     */
    private static function assertRequiredKeysExists(array $keys, array $array): void
    {
        if (array_diff_key(array_flip($keys), $array)) {
            throw new Exception('invalid keys for tax amount');
        }
    }

    /**
     * @return Amount
     */
    public function beforeTaxes(): Amount
    {
        return $this->beforeTaxes;
    }

    /**
     * @return Amount
     */
    public function taxes(): Amount
    {
        return $this->taxes;
    }

    /**
     * @return Amount
     */
    public function afterTaxes(): Amount
    {
        return $this->afterTaxes;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'beforeTaxes' => $this->beforeTaxes()->value(),
            'taxes'       => $this->taxes()->value(),
            'afterTaxes'  => $this->afterTaxes()->value()
        ];
    }

    /**
     * @param Amount $initialAmount initial Amount
     * @return void
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function validateAfterTax(Amount $initialAmount): void
    {
        if (!$this->afterTaxes()->equals($initialAmount)) {
            throw new AfterTaxDoesNotMatchWithAmountException(self::INTIAL_TAX_AMOUNT, self::AMOUNT);
        }
    }

    /**
     * @param Amount $rebillAmount rebill Amount
     * @return void
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function validateRebillAfterTax(Amount $rebillAmount): void
    {
        if (!$this->afterTaxes()->equals($rebillAmount)) {
            throw new AfterTaxDoesNotMatchWithAmountException(self::REBILL_TAX_AMOUNT, self::REBILL_AMOUNT);
        }
    }
}
