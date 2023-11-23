<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateExistingCreditCardBillerFields;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use Tests\UnitTestCase;

class RocketGateExistingCreditCardBillerFieldsTest extends UnitTestCase
{
    private $merchantCustomerId = '12345';

    /**
     * @test
     * @return RocketGateExistingCreditCardBillerFields
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     */
    public function it_should_return_a_biller_fields_object(): RocketGateExistingCreditCardBillerFields
    {
        $billerFields = new RocketGateExistingCreditCardBillerFields(
            '123',
            'pass',
            $this->merchantCustomerId,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            null,
            null
        );

        $this->assertInstanceOf(BillerSettings::class, $billerFields);

        return $billerFields;
    }

    /**
     * @test
     * @depends it_should_return_a_biller_fields_object
     * @param RocketGateExistingCreditCardBillerFields $billerFields The biller fields
     * @return void
     */
    public function it_should_return_an_object_with_the_correct_merchant_customer_id(
        RocketGateExistingCreditCardBillerFields $billerFields
    ): void {
        $this->assertSame($billerFields->merchantCustomerId(), $this->merchantCustomerId);
    }

    /**
     * @test
     * @return void
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     */
    public function it_should_throw_missing_merchant_information_exception_without_merchant_customer_id(): void
    {
        $this->expectException(MissingMerchantInformationException::class);

        new RocketGateExistingCreditCardBillerFields(
            '123',
            'pass',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            null,
            null
        );
    }
}
