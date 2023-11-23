<?php

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class RetrievePumapayQrCodeCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_site_id_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            '',
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_currency_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            '',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_amount_is_negative(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            -29.22,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_business_id_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1,
            null,
            '',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_business_model_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1,
            null,
            'businessId',
            '',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_api_key_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1,
            null,
            'businessId',
            'businessModel',
            '',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_title_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            '',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_description_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            ''
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_rebill_amount_is_set_but_is_not_float(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new Rebill('asd', 3, 3),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_rebill_frequency_is_set_but_is_not_integer(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new Rebill(2.20, 'dfd', 3),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_rebill_start_is_set_but_is_not_integer(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new Rebill(2.20, 5, 'gdfg'),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_rebill_start_or_rebill_frequency_is_set_but_rebill_amount_is_not(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new Rebill(null, 5, 3),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_rebill_amount_is_set_but_rebill_frequency_is_not_set(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new Rebill(2.20, null, 3),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );
    }
}
