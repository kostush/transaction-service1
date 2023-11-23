<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionShouldBeApprovedException;
use ProBillerNG\Transaction\Domain\Model\Exception\RebillNotSetException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use Tests\IntegrationTestCase;

class AddBillerInteractionForRebillOnPumapayCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|PumapayService */
    protected $pumapayService;

    /** @var MockObject|PumapayRebillPostbackHttpCommandDTOAssembler */
    protected $dtoAssembler;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var AddBillerInteractionForRebillOnPumapayCommandHandler */
    protected $handler;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository     = $this->createMock(TransactionRepository::class);
        $this->pumapayService = $this->createMock(PumapayService::class);
        $this->bi             = $this->createMock(BILoggerService::class);
        $this->pumapayService->method('translatePostback')->willReturn(
            PumapayPostbackBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'status'   => 'approved',
                        'type'     => 'rebill',
                        'response' => [],
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $this->handler = new AddBillerInteractionForRebillOnPumapayCommandHandler(
            $this->repository,
            new PumapayRebillPostbackHttpCommandDTOAssembler,
            $this->pumapayService,
            $this->bi
        );
    }

    /**
     * @test
     * @return array
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingTransactionInformationException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): array
    {
        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Approved::create());
        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            (string) $previousTransaction->transactionId(),
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(PumapayRebillPostbackCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     *
     * @param array $response Response
     *
     * @return void
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     */
    public function it_should_have_a_transaction_id(array $response): void
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     *
     * @param array $response Response
     *
     * @return void
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     */
    public function it_should_have_a_status(array $response): void
    {
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * @test
     *
     * @param array $response Response
     *
     * @return void
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     */
    public function it_should_have_an_approved_status(array $response): void
    {
        $this->assertSame('approved', $response['status']);
    }

    /**
     * @test
     * @return void
     * @throws MissingTransactionInformationException
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_previous_transaction_id_is_not_uuid(): void
    {
        $this->expectException(InvalidTransactionInformationException::class);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            'aaaa',
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws MissingTransactionInformationException
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_previous_transaction_not_found(): void
    {
        $this->expectException(PreviousTransactionNotFoundException::class);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            $this->faker->uuid,
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $this->repository->method('findById')->willReturn(null);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingTransactionInformationException
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_previous_transaction_status_not_approved(): void
    {
        $this->expectException(PreviousTransactionShouldBeApprovedException::class);

        $previousTransaction = $this->createChargeTransactionWithoutRebillOnPumapay();
        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            (string) $previousTransaction->transactionId(),
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws MissingTransactionInformationException
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_previous_transaction_not_pumapay(): void
    {
        $this->expectException(InvalidBillerNameException::class);

        $rocketgateTransaction = $this->createPendingRocketgateTransactionSingleCharge();
        $this->repository->method('findById')->willReturn($rocketgateTransaction);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            (string) $rocketgateTransaction->transactionId(),
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingTransactionInformationException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_rebill_charge_information_is_null(): void
    {
        $this->expectException(RebillNotSetException::class);

        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();
        $reflection  = new \ReflectionObject($transaction);
        $attribute   = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($transaction, Approved::create());
        $this->repository->method('findById')->willReturn($transaction);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            (string) $transaction->transactionId(),
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],

            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingTransactionInformationException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function it_should_write_the_bi_event(): void
    {
        $this->bi->expects($this->once())->method('write');

        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Approved::create());
        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AddBillerInteractionForRebillOnPumapayCommand(
            (string) $previousTransaction->transactionId(),
            [
                'payload' => [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                    ],
                    'paymentData'     => [],
                ],
            ]
        );

        $handler = new AddBillerInteractionForRebillOnPumapayCommandHandler(
            $this->repository,
            new PumapayRebillPostbackHttpCommandDTOAssembler,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($command);
    }
}
