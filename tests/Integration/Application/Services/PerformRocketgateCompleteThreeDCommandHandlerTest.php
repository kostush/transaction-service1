<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\IntegrationTestCase;

class PerformRocketgateCompleteThreeDCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var ChargeService
     */
    private $chargeService;

    /**
     * @var TransactionRepository
     */
    private $transactionRepositoryMock;

    /**
     * @var BILoggerService
     */
    private $biLoggerMock;

    /**
     * @var PerformRocketgateCompleteThreeDCommand
     */
    private $command;

    /**
     * @var MockObject|RedisRepository
     */
    private $redisRepoMock;

    /**
     * @var MockObject|DeclinedBillerResponseExtraDataRepository
     */
    private $declinedBillerResponseExtraDataRepository;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->command = new PerformRocketgateCompleteThreeDCommand(
            $this->faker->uuid,
            'SimulatedPARES10001000E00B000',
            ''
        );

        $this->chargeService                             = $this->createMock(ChargeService::class);
        $this->declinedBillerResponseExtraDataRepository = $this->createMock(DeclinedBillerResponseExtraDataRepository::class);
        $this->chargeService->method('completeThreeDCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => ["reasonCode" => "0", "responseCode" => "0", "reasonDesc" => "Success"],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $this->transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $this->redisRepoMock             = $this->createMock(RedisRepository::class);
        $this->biLoggerMock              = $this->createMock(BILoggerService::class);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided_with_pares(): void
    {
        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $result = $handler->execute($this->command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided_with_md(): void
    {
        $command = new PerformRocketgateCompleteThreeDCommand(
            $this->faker->uuid,
            '',
            '10001732904A027'
        );

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function it_should_throw_exception_when_previous_transaction_is_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->transactionRepositoryMock->method('findById')->willReturn(null);

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function it_should_throw_exception_when_transaction_does_not_have_a_pending_status(): void
    {
        $this->expectException(InvalidStatusException::class);

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ["request" => "json"],
                    'response' => ["reasonCode" => "0", "responseCode" => "0", "reasonDesc" => "Success"],
                    'reason'   => '111',
                    'code'     => '1',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );
        $previousTransaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_write_bi_event(): void
    {
        $this->biLoggerMock->expects($this->once())->method('write');

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided_with_md_with_cvv_from_redis(): void
    {
        $command = new PerformRocketgateCompleteThreeDCommand(
            $this->faker->uuid,
            '',
            '10001732904A027'
        );

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $this->redisRepoMock->method('retrieveCvv')->willReturn('344');
        $this->redisRepoMock->expects($this->once())->method('retrieveCvv');
        $this->redisRepoMock->expects($this->once())->method('deleteCvv');

        $handler = new PerformRocketgateCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->redisRepoMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }
}
