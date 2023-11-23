<?php


namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use Tests\UnitTestCase;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommand;

class PerformNetbillingUpdateRebillCommandTest extends UnitTestCase
{

    private $newCCPayment;

    private $existingCardPayment;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newCCPayment = new Payment(
            'cc',
            new NewCreditCardInformation(
                $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
                '10',
                2022,
                '321',
                new Member(
                    $this->faker->name,
                    $this->faker->lastName,
                    $this->faker->userName,
                    $this->faker->email,
                    $this->faker->phoneNumber,
                    $this->faker->address,
                    $this->faker->postcode,
                    $this->faker->city,
                    'state',
                    'country'
                )
            ),
        );

        $this->existingCardPayment = new Payment(
            'cc',
            new ExistingCreditCardInformation(
                'm77xlHZiPKVsF9p1/VdzTb+CUwaGBDpuSRxtcb7+j24='
            )
        );
    }

    /**
     * @test
     * @return PerformNetbillingUpdateRebillCommand
     * @throws InvalidChargeInformationException
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function create_with_new_card_should_return_command_instance(): PerformNetbillingUpdateRebillCommand
    {
        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];

        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'rebill'           => $rebil,
                'payment'          => $this->newCCPayment,
            ]
        );

        $this->assertInstanceOf(PerformNetbillingUpdateRebillCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_amount(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame(20.0, $command->rebillAmount());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_start(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame(365, $command->rebillStart());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_rebill_frequency(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame(365, $command->rebillFrequency());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_payment_type(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame('cc', $command->paymentType());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_cc_number(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], $command->payment()->information()->number());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_expiration_month(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame(10, $command->payment()->information()->expirationMonth());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_expiration_year(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame(2022, $command->payment()->information()->expirationYear());
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_new_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_cvv(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame('321', $command->payment()->information()->cvv());
    }

    /**
     * @test
     * @return PerformNetbillingUpdateRebillCommand
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function create_with_existing_card_should_return_command_instance(): PerformNetbillingUpdateRebillCommand
    {
        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];

        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'rebill'           => $rebil,
                'payment'          => $this->existingCardPayment
            ]
        );

        $this->assertInstanceOf(PerformNetbillingUpdateRebillCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformNetbillingUpdateRebillCommand $command Update Rebill Command
     * @depends create_with_existing_card_should_return_command_instance
     * @return void
     */
    public function it_should_return_correct_card_hash(PerformNetbillingUpdateRebillCommand $command): void
    {
        $this->assertSame('m77xlHZiPKVsF9p1/VdzTb+CUwaGBDpuSRxtcb7+j24=',
            $command->payment()->information()->cardHash());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_not_throw_exception_when_amount_is_provided_without_currency(): void
    {
        $rebil = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];

        $payment = new Payment(
            'cc',
            new NewCreditCardInformation(
                $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
                '10',
                2022,
                '321',
                new Member(
                    $this->faker->name,
                    $this->faker->lastName,
                    $this->faker->userName,
                    $this->faker->email,
                    $this->faker->phoneNumber,
                    $this->faker->address,
                    $this->faker->postcode,
                    $this->faker->city,
                    'state',
                    'country'
                )
            ),
        );
        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'amount'   => 20,
                'currency' => '',
                'rebill'   => $rebil,
                'payment'  => $payment,
            ]
        );
        $this->assertInstanceOf(PerformNetbillingUpdateRebillCommand::class, $command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_amount_is_provided_without_payment_information(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'amount'  => 20,
                'rebill'  => $rebill,
                'payment' => new Payment(
                    'rr',
                    new ExistingCreditCardInformation(
                        'm77xlHZiPKVsF9p1/VdzTb+CUwaGBDpuSRxtcb7+j24='
                    )
                )
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_invalid_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'amount'  => 'invalid',
                'payment' => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_empty_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'amount'  => '',
                'payment' => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_invalid_rebill_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 'invalid',
            'start'     => 365,
            'frequency' => 365
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_invalid_rebill_start_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => true,
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_invalid_rebill_frequency_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => false,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_invalid_payment_method_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];
        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'amount'  => 20,
                'rebill'  => $rebill,
                'payment' => new Payment(
                    'invalid',
                    new ExistingCreditCardInformation(
                        'm77xlHZiPKVsF9p1/VdzTb+CUwaGBDpuSRxtcb7+j24='
                    )
                )
            ]
        );
    }


    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_rebill_amount_is_zero_and_frequency_is_not(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 0,
            'start'     => 365,
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_rebill_frequency_is_zero_and_amount_is_not(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 0,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_empty_rebill_frequency_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => '',
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_empty_rebill_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => '',
            'start'     => 365,
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_empty_rebill_start_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'start'     => '',
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_no_rebill_amount_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'start'     => 365,
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_no_rebill_start_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount'    => 20,
            'frequency' => 365,
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function it_should_throw_exception_when_no_rebill_frequency_is_provided(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $rebill = [
            'amount' => 20,
            'start'  => 365
        ];

        $this->createPerformNetbillingUpdateRebillCommand(
            [
                'rebill' => $rebill,
                'payment'   => $this->existingCardPayment
            ]
        );
    }
}