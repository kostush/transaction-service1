<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use Tests\UnitTestCase;

class PerformRocketgateUpdateRebillCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return PerformRocketgateUpdateRebillCommand
     */
    public function create_with_new_card_should_return_command_instance(): PerformRocketgateUpdateRebillCommand
    {
        $rebil   = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $payment = [
            'method'      => 'cc',
            'information' => [
                'number'          => $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
                'expirationMonth' => 10,
                'expirationYear'  => 2022,
                'cvv'             => '321',
            ]
        ];
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'rebill'  => $rebil,
                'payment' => $payment,
            ]
        );

        $this->assertInstanceOf(PerformRocketgateUpdateRebillCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_amount(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame(20.0, $command->rebillAmount());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_start(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame(365, $command->rebillStart());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_frequency(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame(365, $command->rebillFrequency());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_payment_type(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame('cc', $command->paymentType());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_cc_number(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], $command->ccNumber());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_expiration_month(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame(10, $command->cardExpirationMonth());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_expiration_year(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame(2022, $command->cardExpirationYear());
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_cvv(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame('321', $command->cvv());
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return PerformRocketgateUpdateRebillCommand
     */
    public function create_with_existing_card_should_return_command_instance(): PerformRocketgateUpdateRebillCommand
    {
        $rebil   = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $payment = [
            'method'      => 'cc',
            'information' => [
                'cardHash' => $_ENV['ROCKETGATE_CARD_HASH_1']
            ]
        ];
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'rebill'  => $rebil,
                'payment' => $payment,
            ]
        );

        $this->assertInstanceOf(PerformRocketgateUpdateRebillCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformRocketgateUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_existing_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_hash(PerformRocketgateUpdateRebillCommand $command): void
    {
        $this->assertSame($_ENV['ROCKETGATE_CARD_HASH_1'], $command->cardHash());
    }

    /**
     * @test
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function it_should_return_correct_merchant_account_when_provided(): void
    {
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantAccount' => "1",
            ]
        );

        $this->assertSame("1", $command->merchantAccount());
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_amount_is_provided_without_currency(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil   = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $payment = [
            'method'      => 'cc',
            'information' => [
                'number'          => $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
                'expirationMonth' => 10,
                'expirationYear'  => 2022,
                'cvv'             => '321',
            ]
        ];
        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'amount'   => 20,
                'currency' => '',
                'rebill'   => $rebil,
                'payment'  => $payment,
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_amount_is_provided_without_payment_information(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'amount'  => 20,
                'rebill'  => $rebil,
                'payment' => [],
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'amount' => 'invalid'
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_rebill_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil = [
            'amount'    => 'invalid',
            'start'     => 365,
            'frequency' => 365,
        ];

        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'rebill' => $rebil
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_rebill_start_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil = [
            'amount'    => 20,
            'start'     => true,
            'frequency' => 365,
        ];

        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'rebill' => $rebil
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_rebill_frequency_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => false,
        ];

        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'rebill' => $rebil
            ]
        );
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_payment_method_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $this->createPerformRocketgateUpdateRebillCommand(
            [
                'amount'  => 20,
                'rebill'  => $rebil,
                'payment' => [
                    'method' => 'invalid'
                ],
            ]
        );
    }
}
