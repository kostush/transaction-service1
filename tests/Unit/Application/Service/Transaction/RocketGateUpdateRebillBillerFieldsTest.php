<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateUpdateRebillBillerFields;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use Tests\UnitTestCase;

class RocketGateUpdateRebillBillerFieldsTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $merchantCustomerId = '12345';

    /**
     * @var string
     */
    private $merchantInvoiceId = '123123';

    /**
     * @var string
     */
    private $merchantAccount = '1';

    /**
     * @test
     * @return RocketGateUpdateRebillBillerFields
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     */
    public function it_should_return_a_biller_fields_object(): RocketGateUpdateRebillBillerFields
    {
        $billerFields = new RocketGateUpdateRebillBillerFields(
            (string) $this->faker->numberBetween(10000, 99999),
            $this->faker->password,
            $this->merchantCustomerId,
            $this->merchantInvoiceId,
            $this->merchantAccount
        );

        $this->assertInstanceOf(BillerSettings::class, $billerFields);

        return $billerFields;
    }

    /**
     * @test
     * @depends it_should_return_a_biller_fields_object
     * @param RocketGateUpdateRebillBillerFields $billerFields The biller fields
     * @return void
     */
    public function it_should_return_an_object_with_the_correct_merchant_customer_id(
        RocketGateUpdateRebillBillerFields $billerFields
    ): void {
        $this->assertSame($this->merchantCustomerId, $billerFields->merchantCustomerId());
    }

    /**
     * @test
     * @depends it_should_return_a_biller_fields_object
     * @param RocketGateUpdateRebillBillerFields $billerFields The biller fields
     * @return void
     */
    public function it_should_return_an_object_with_the_correct_merchant_invoice_id(
        RocketGateUpdateRebillBillerFields $billerFields
    ): void {
        $this->assertSame($this->merchantInvoiceId, $billerFields->merchantInvoiceId());
    }

    /**
     * @test
     * @depends it_should_return_a_biller_fields_object
     * @param RocketGateUpdateRebillBillerFields $billerFields The biller fields
     * @return void
     */
    public function it_should_return_an_object_with_the_correct_merchant_account_id(
        RocketGateUpdateRebillBillerFields $billerFields
    ): void {
        $this->assertSame($this->merchantAccount, $billerFields->merchantAccount());
    }

    /**
     * @test
     * @return void
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     */
    public function it_should_throw_missing_merchant_information_exception_without_merchant_customer_id(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        new RocketGateUpdateRebillBillerFields(
            (string) $this->faker->numberBetween(10000, 99999),
            (string) $this->faker->password,
            null,
            null,
            null
        );
    }

    /**
     * @test
     * @return void
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     */
    public function it_should_throw_missing_merchant_information_exception_without_merchant_invoice_id(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        new RocketGateUpdateRebillBillerFields(
            (string) $this->faker->numberBetween(10000, 99999),
            (string) $this->faker->password,
            (string) $this->faker->numberBetween(10000, 99999),
            null,
            null
        );
    }
}
