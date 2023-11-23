<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Currency;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use Tests\UnitTestCase;

class CurrencyTest extends UnitTestCase
{
    /**
     * @test
     * @return Currency
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function create_currency_should_throw_exception_when_incorrect_code_is_provided()
    {
        $this->expectException(InvalidChargeInformationException::class);

        $currency = $this->createCurrency(['currency' => 'invalidCurrency']);

        return $currency;
    }

    /**
     * @test
     * @depends create_currency_should_throw_exception_when_incorrect_code_is_provided
     * @return Currency
     * @throws \Exception
     */
    public function create_should_return_currency_when_correct_code_is_provided()
    {
        $currency = $this->createCurrency();

        $this->assertInstanceOf(Currency::class, $currency);

        return $currency;
    }

    /**
     * @test
     * @depends create_should_return_currency_when_correct_code_is_provided
     * @param Currency $currency Currency object
     * @return void
     * @throws \Exception
     */
    public function currency_should_return_true_when_equal_currency(Currency $currency)
    {
        $equalCurrency = $this->createCurrency();

        $this->assertTrue($currency->equals($equalCurrency));
    }

    /**
     * @test
     * @depends create_should_return_currency_when_correct_code_is_provided
     * @param Currency $currency Currency object
     * @return void
     * @throws \Exception
     */
    public function currency_should_return_false_when_equal_currency(Currency $currency)
    {
        $equalCurrency = $this->createCurrency(['currency' => 'EUR']);

        $this->assertFalse($currency->equals($equalCurrency));
    }

    /**
     * @test
     * @dataProvider invalidCurrencyCodeProvider
     * @param string $currencyValue currency values
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function create_invalid_code_should_throw_missing_charge_information_exception(string $currencyValue): void
    {
        $this->expectException(InvalidChargeInformationException::class);
        Currency::create($currencyValue);
    }

    /**
     * @return array
     */
    public function invalidCurrencyCodeProvider(): array
    {
        return [
            'invalid string'    => ['invalid'],
            'special chars'     => ['$$$'],
            'more than 3 chars' => ['WERT'],
            'less than 3 chars' => ['AV'],
            'numeric'           => ['123'],
            'invalid code'      => ['YYY']
        ];
    }
}
