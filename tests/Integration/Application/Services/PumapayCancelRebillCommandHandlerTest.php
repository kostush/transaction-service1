<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayCancelRebillCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayCancelRebillHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionShouldBeApprovedException;
use ProBillerNG\Transaction\Domain\Model\Exception\RebillNotSetException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use Tests\IntegrationTestCase;

class PumapayCancelRebillCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|PumapayService */
    protected $pumapayService;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var PumapayCancelRebillCommandHandler */
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
        $this->pumapayService->method('cancelRebill')->willReturn(
            PumapayCancelRebillBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'success'  => true,
                        'request'  => [
                            'businessId' => 'aaa',
                            'paymentId'  => 'bbb',
                        ],
                        'response' => [
                            'success' => true,
                            'status'  => 200
                        ],
                        'code'     => 200,
                        'reason'   => null
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $this->handler = new PumapayCancelRebillCommandHandler(
            $this->repository,
            new PumapayCancelRebillHttpCommandDTOAssembler,
            $this->pumapayService,
            $this->bi
        );
    }

    /**
     * @test
     * @return array
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ReflectionException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): array
    {
        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Approved::create());
        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new PumapayCancelRebillCommand(
            (string) $previousTransaction->transactionId(),
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(PumapayCancelRebillCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     */
    public function it_should_have_a_transaction_id(array $response): void
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     */
    public function it_should_have_a_status(array $response): void
    {
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * @test
     * @param array $response Response
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
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_throw_exception_when_transaction_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $command = new PumapayCancelRebillCommand(
            $this->faker->uuid,
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_throw_exception_when_transaction_is_not_charge(): void
    {
        $this->expectException(InvalidTransactionTypeException::class);

        $previousTransaction     = $this->createChargeTransactionWithRebillOnPumapay();
        $rebillUpdateTransaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);

        $this->repository->method('findById')->willReturn($rebillUpdateTransaction);

        $command = new PumapayCancelRebillCommand(
            (string) $rebillUpdateTransaction->transactionId(),
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_throw_exception_when_transaction_is_not_pumapay(): void
    {
        $this->expectException(InvalidBillerNameException::class);

        $rocketgateTransaction = $this->createPendingRocketgateTransactionSingleCharge();
        $this->repository->method('findById')->willReturn($rocketgateTransaction);

        $command = new PumapayCancelRebillCommand(
            (string) $rocketgateTransaction->transactionId(),
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_throw_exception_when_transaction_status_is_not_approved(): void
    {
        $this->expectException(PreviousTransactionShouldBeApprovedException::class);

        $transaction = $this->createChargeTransactionWithRebillOnPumapay();
        $this->repository->method('findById')->willReturn($transaction);

        $command = new PumapayCancelRebillCommand(
            (string) $transaction->transactionId(),
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_invalid_transaction_information_exception_when_id_is_not_uuid(): void
    {
        $this->expectException(InvalidTransactionInformationException::class);

        $command = $this->createMock(PumapayCancelRebillCommand::class);
        $command->method('transactionId')->willReturn('asdasd');
        $this->repository->method('findById')->willReturn(null);

        $handler = new PumapayCancelRebillCommandHandler(
            $this->repository,
            new PumapayCancelRebillHttpCommandDTOAssembler,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ReflectionException
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

        $command = new PumapayCancelRebillCommand(
            (string) $transaction->transactionId(),
            'businessId',
            'businessModel',
            'apiKey'
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ReflectionException
     */
    public function it_should_write_the_bi_event(): void
    {
        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Approved::create());
        $this->repository->method('findById')->willReturn($previousTransaction);

        $this->bi->expects($this->once())->method('write');

        $command = new PumapayCancelRebillCommand(
            (string) $previousTransaction->transactionId(),
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_BUSINESS_MODEL'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $handler = new PumapayCancelRebillCommandHandler(
            $this->repository,
            new PumapayCancelRebillHttpCommandDTOAssembler,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($command);
    }
}
