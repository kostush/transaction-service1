<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\Charge;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\Currency;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;
use ProBillerNG\Transaction\Domain\Model\Rebill as ChargeInformationRebill;

class ChargeInformationTest extends UnitTestCase
{
    /**
     * @test
     * @return ChargeInformation
     * @throws \Exception
     */
    public function create_should_return_charge_information_when_correct_data_is_provided()
    {
        $chargeInformation = $this->createChargeInformationWithRebill();

        $this->assertInstanceOf(ChargeInformation::class, $chargeInformation);

        return $chargeInformation;
    }

    /**
     * @test
     * @depends create_should_return_charge_information_when_correct_data_is_provided
     * @param ChargeInformation $chargeInformation Charge Information object
     * @return void
     * @throws \Exception
     */
    public function charge_information_should_return_true_when_equal(ChargeInformation $chargeInformation)
    {
        $equalChargeInformation = $this->createChargeInformationWithRebill(
            [
                'currency'     => $chargeInformation->currency()->code(),
                'amount'       => $chargeInformation->amount()->value(),
                'frequency'    => $chargeInformation->rebill()->frequency(),
                'start'        => $chargeInformation->rebill()->start(),
                'rebillAmount' => $chargeInformation->rebill()->amount()->value()
            ]
        );

        $this->assertTrue($chargeInformation->equals($equalChargeInformation));
    }

    /**
     * @test
     * @depends create_should_return_charge_information_when_correct_data_is_provided
     * @param ChargeInformation $chargeInformation Charge Information object
     * @return void
     * @throws \Exception
     */
    public function charge_information_should_return_false_when_not_equal(ChargeInformation $chargeInformation)
    {
        $equalChargeInformation = $this->createChargeInformationWithRebill(['currency' => 'EUR']);

        $this->assertFalse($chargeInformation->equals($equalChargeInformation));
    }

    /**
     * @test
     * @return ChargeInformation
     * @throws \Exception
     */
    public function create_should_return_charge_information_when_null_rebill_data_is_provided()
    {
        $chargeInformation = $this->createChargeInformationSingleCharge();

        $this->assertInstanceOf(ChargeInformation::class, $chargeInformation);

        return $chargeInformation;
    }

    /**
     * @test
     * @depends create_should_return_charge_information_when_null_rebill_data_is_provided
     * @param ChargeInformation $chargeInformation Charge Information object
     * @return void
     * @throws \Exception
     */
    public function charge_information_with_null_rebill_should_return_true_when_equal(ChargeInformation $chargeInformation)
    {
        $equalChargeInformation = $this->createChargeInformationSingleCharge(
            [
                'currency' => $chargeInformation->currency()->code(),
                'amount'   => $chargeInformation->amount()->value()
            ]
        );

        $this->assertTrue($chargeInformation->equals($equalChargeInformation));
    }

    /**
     * @test
     * @depends create_should_return_charge_information_when_correct_data_is_provided
     * @param ChargeInformation $chargeInformation Charge Information object
     * @return void
     * @throws \Exception
     */
    public function charge_information_with_null_rebill_should_return_false_when_not_equal(ChargeInformation $chargeInformation)
    {
        $equalChargeInformation = $this->createChargeInformationSingleCharge(['currency' => 'EUR']);

        $this->assertFalse($chargeInformation->equals($equalChargeInformation));
    }

    /**
     * @test
     * @group legacyService
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_create_with_rebill_charge_information_from_charge(): void
    {
        $amount       = $this->faker->randomFloat(2);
        $rebillAmount = $this->faker->randomFloat(2);
        $frequency    = $this->faker->randomNumber();
        $start        = $this->faker->randomNumber();
        $currency     = $this->faker->currencyCode;

        $mockedRebill = $this->createMock(ChargeInformationRebill::class);
        $mockedRebill->method('amount')->willReturn(Amount::create($rebillAmount));
        $mockedRebill->method('start')->willReturn($start);
        $mockedRebill->method('frequency')->willReturn($frequency);

        $mockedCharge = $this->createMock(Charge::class);
        $mockedCharge->method('rebill')
            ->willReturn($mockedRebill);

        $mockedCharge->method('currency')->willReturn(Currency::create($currency));
        $mockedCharge->method('amount')->willReturn(Amount::create($amount));

        $chargeInformation = ChargeInformation::createChargeInformationFromCharge($mockedCharge);

        $this->assertNotEmpty($chargeInformation->rebill());
        $this->assertEquals($currency, (string) $chargeInformation->currency());
        $this->assertEquals($amount, (string) $chargeInformation->amount());
        $this->assertEquals($start, $chargeInformation->rebill()->start());
        $this->assertEquals($frequency, $chargeInformation->rebill()->frequency());
        $this->assertEquals($rebillAmount, (string) $chargeInformation->rebill()->amount());
    }

    /**
     * @test
     * @group legacyService
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_create_single_charge_information_from_charge(): void
    {
        $amount   = $this->faker->randomFloat(2);
        $currency = $this->faker->currencyCode;

        $mockedCharge = $this->createMock(Charge::class);
        $mockedCharge->method('rebill')->willReturn(null);
        $mockedCharge->method('currency')->willReturn(Currency::create($currency));
        $mockedCharge->method('amount')->willReturn(Amount::create($amount));

        $chargeInformation = ChargeInformation::createChargeInformationFromCharge($mockedCharge);

        $this->assertEmpty($chargeInformation->rebill());
        $this->assertEquals($amount, (string) $chargeInformation->amount());
        $this->assertEquals($currency, (string) $chargeInformation->currency());
    }

    /**
     * @test
     * @group legacyService
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_create_single_charge_information_from_command_data(): void
    {
        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $this->faker->uuid
        );

        $chargeInformation = ChargeInformation::createFromCommand(
            $command->amount(),
            $command->currency(),
            $command->rebill()
        );

        $this->assertEmpty($chargeInformation->rebill());
        $this->assertEquals($command->amount(), (string) $chargeInformation->amount());
        $this->assertEquals($command->currency(), (string) $chargeInformation->currency());
    }

    /**
     * @test
     * @group legacyService
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_create_rebill_charge_information_from_command_data(): void
    {
        $rebill = new Rebill(
            10,
            11,
            12
        );

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            $rebill,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $this->faker->uuid
        );

        $chargeInformation = ChargeInformation::createFromCommand(
            $command->amount(),
            $command->currency(),
            $command->rebill()
        );

        $this->assertEquals($command->amount(), (string) $chargeInformation->amount());
        $this->assertEquals($command->currency(), (string) $chargeInformation->currency());
        $this->assertEquals($rebill->amount(), $chargeInformation->rebill()->amount()->value());
        $this->assertEquals($rebill->start(), $chargeInformation->rebill()->start());
        $this->assertEquals($rebill->frequency(), $chargeInformation->rebill()->frequency());
    }
}
