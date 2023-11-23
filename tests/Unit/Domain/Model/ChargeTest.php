<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Charge;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class ChargeTest
 * @package Tests\Unit\Domain\Model
 */
class ChargeTest extends UnitTestCase
{

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function it_should_create_charge_with_no_amout_nor_rebill(): void
    {
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;

        $charge = Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            null,
            null,
            null,
            null
        );

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertEquals($currency, (string) $charge->currency());
        $this->assertEquals($productId, $charge->productId());
        $this->assertEquals($isMainPurchase, $charge->isMainPurchase());
        $this->assertEmpty($charge->amount()->value());
        $this->assertEmpty($charge->rebill());
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function it_should_create_charge_with_amount_and_rebill(): void
    {
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = $this->faker->randomFloat(2);
        $rebill         = [
            'frequency' => $this->faker->randomNumber(),
            'start'     => $this->faker->randomNumber(),
            'amount'    => $this->faker->randomFloat(2)
        ];

        $charge = Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            $rebill,
            null
        );

        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertEquals($currency, (string) $charge->currency());
        $this->assertEquals($productId, $charge->productId());
        $this->assertEquals($isMainPurchase, $charge->isMainPurchase());
        $this->assertEquals($amount, (string) $charge->amount());
        $this->assertNotEmpty($charge->rebill());
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function charge_should_throws_exception_when_initial_amount_after_tax_does_not_match(): void
    {
        $this->expectException(AfterTaxDoesNotMatchWithAmountException::class);
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = 10.2;

        $tax = [
            "initialAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => 10.99
            ],
            "taxApplicationId"     => "60bf5bcb-ac64-496c-acc5-9c7cf54a1869",
            "taxName"              => "VAT",
            "taxType"              => "vat",
            "taxRate"              => 0.05,
            "displayChargedAmount" => true
        ];

        Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            null,
            $tax
        );
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function charge_should_throws_exception_when_rebill_amount_after_tax_does_not_match(): void
    {
        $this->expectException(AfterTaxDoesNotMatchWithAmountException::class);
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = 20.2;
        $rebill         = [
            'frequency' => 365,
            'start'     => 365,
            'amount'    => "10.5"
        ];

        $tax = [
            "initialAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => "20.2"
            ],
            "rebillAmount"         => [
                "beforeTaxes" => 34.97,
                "taxes"       => 0.5,
                "afterTaxes"  => "10.4"
            ],
            "taxApplicationId"     => "60bf5bcb-ac64-496c-acc5-9c7cf54a1869",
            "taxName"              => "VAT",
            "taxType"              => "vat",
            "taxRate"              => 0.05,
            "displayChargedAmount" => true
        ];

        Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            $rebill,
            $tax
        );
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function it_should_create_charge_when_after_taxes_match(): void
    {
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = 10.2;
        $rebill         = [
            'frequency' => 365,
            'start'     => 365,
            'amount'    => "10.4"
        ];

        $tax = [
            "initialAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => "10.2"
            ],
            "rebillAmount"         => [
                "beforeTaxes" => 34.97,
                "taxes"       => 0.5,
                "afterTaxes"  => "10.4"
            ],
            "taxApplicationId"     => "60bf5bcb-ac64-496c-acc5-9c7cf54a1869",
            "taxName"              => "VAT",
            "taxType"              => "vat",
            "taxRate"              => 0.05,
            "displayChargedAmount" => true
        ];

        $charge = Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            $rebill,
            $tax
        );

        $this->assertInstanceOf(Charge::class, $charge);
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     */
    public function it_should_create_charge_with_string_rebill_amount(): void
    {
        $expectedAmount = "10.2";

        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = $this->faker->randomFloat(2);
        $rebill         = [
            'frequency' => $this->faker->randomNumber(),
            'start'     => $this->faker->randomNumber(),
            'amount'    => $expectedAmount
        ];

        $charge = Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            $rebill,
            null
        );

        $this->assertEquals($expectedAmount, $charge->rebill()->amount()->value());
    }

    /**
     * @test
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_create_charge_with_rebill_and_initial_tax_but_without_rebill_tax(): void
    {
        $currency       = $this->faker->currencyCode;
        $productId      = $this->faker->randomNumber();
        $siteId         = $this->faker->uuid;
        $isMainPurchase = true;
        $amount         = 10.0;
        $rebill         = [
            'frequency' => $this->faker->randomNumber(),
            'start'     => $this->faker->randomNumber(),
            'amount'    => 10.0
        ];

        $tax = [
            "initialAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => 10.0
            ],
            "taxApplicationId"     => "60bf5bcb-ac64-496c-acc5-9c7cf54a1869",
            "taxName"              => "VAT",
            "taxType"              => "vat",
            "taxRate"              => 0.05,
            "displayChargedAmount" => true
        ];

        $charge = Charge::create(
            $currency,
            $productId,
            $siteId,
            $isMainPurchase,
            $amount,
            null,
            $rebill,
            $tax
        );

        $this->assertInstanceOf(Charge::class, $charge);
    }
}
