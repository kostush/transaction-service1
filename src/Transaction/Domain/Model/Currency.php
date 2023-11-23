<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class Currency
{
    /**
     * @var string
     */
    private $code;

    /**
     * Currency constructor.
     * @param string|null $code Currency Code
     * @throws \InvalidArgumentException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function __construct(?string $code)
    {
        $this->code = $this->initCurrencyCode($code);
    }

    /**
     * @param string $code Currency Code
     * @return Currency
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(?string $code): self
    {
        return new static($code);
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * @param Currency $currency Currency to compare
     * @return bool
     */
    public function equals(Currency $currency): bool
    {
        return $this->code() === $currency->code();
    }

    /**
     * @param string|null $currencyCode Currency code
     * @return string
     * @throws \InvalidArgumentException
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initCurrencyCode(?string $currencyCode): string
    {
        $currencies = new ISOCurrencies();
        if (!$currencies->contains(new MoneyCurrency(mb_strtoupper($currencyCode)))) {
            throw new InvalidChargeInformationException('currency:' . $currencyCode);
        }

        return $currencyCode;
    }
}
