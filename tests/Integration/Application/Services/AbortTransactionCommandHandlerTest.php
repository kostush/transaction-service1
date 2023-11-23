<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\AbortCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\AbortHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\AbortTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use Tests\IntegrationTestCase;

class AbortTransactionCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|AbortTransactionDTOAssembler */
    protected $dtoAssembler;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var AbortTransactionCommandHandler */
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
        $this->bi             = $this->createMock(BILoggerService::class);

        $this->handler = new AbortTransactionCommandHandler(
            $this->repository,
            new AbortHttpCommandDTOAssembler(),
            $this->bi
        );
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): array
    {
        $previousTransaction = $this->createChargeTransactionWithoutRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Pending::create());
        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AbortTransactionCommand(
            (string) $previousTransaction->transactionId()
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(AbortCommandHttpDTO::class, $result);

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
    public function it_should_have_an_aborted_status(array $response): void
    {
        $this->assertSame('aborted', $response['status']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_transaction_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $command = new AbortTransactionCommand(
            $this->faker->uuid
        );

        $this->repository->method('findById')->willReturn(null);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_transaction_was_processed(): void
    {
        $this->expectException(TransactionAlreadyProcessedException::class);

        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Approved::create());

        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AbortTransactionCommand(
            (string) $previousTransaction->transactionId()
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_write_the_bi_event(): void
    {
        $this->bi->expects($this->once())->method('write');

        $previousTransaction = $this->createChargeTransactionWithRebillOnPumapay();
        $reflection          = new \ReflectionObject($previousTransaction);
        $attribute           = $reflection->getProperty('status');
        $attribute->setAccessible(true);
        $attribute->setValue($previousTransaction, Pending::create());

        $this->repository->method('findById')->willReturn($previousTransaction);

        $command = new AbortTransactionCommand(
            (string) $previousTransaction->transactionId()
        );

        $handler = new AbortTransactionCommandHandler(
            $this->repository,
            new AbortHttpCommandDTOAssembler(),
            $this->bi
        );

        $handler->execute($command);
    }
}
