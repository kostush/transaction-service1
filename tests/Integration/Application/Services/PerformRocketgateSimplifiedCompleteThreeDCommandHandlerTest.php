<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\IntegrationTestCase;

class PerformRocketgateSimplifiedCompleteThreeDCommandHandlerTest extends IntegrationTestCase
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
     * @var PerformRocketgateSimplifiedCompleteThreeDCommand
     */
    private $command;

    /**
     * @var MockObject|DeclinedBillerResponseExtraDataRepository
     */
    private $declinedBillerResponseExtraDataRepository;

    /**
     * Setup test
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->command = new PerformRocketgateSimplifiedCompleteThreeDCommand(
            $this->faker->uuid,
            'flag=17c30f49482&id=3DS-Simplified&invoiceID=1632908872&hash=dQwDg2FEFDVBQ%2BxpWR0tqdA3Y0s%3D'
        );

        $this->chargeService                             = $this->createMock(ChargeService::class);
        $this->declinedBillerResponseExtraDataRepository = $this->createMock(DeclinedBillerResponseExtraDataRepository::class);
        $this->chargeService->method('simplifiedCompleteThreeD')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => ["reasonCode" => "0", "responseCode" => "0", "reasonDesc" => "Success"],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $this->biLoggerMock              = $this->createMock(BILoggerService::class);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws TransactionCreationException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): void
    {
        $previousTransaction = $this->createPendingTransactionWithRebillForExistingCreditCard(['requiredToUse3D' => true]);

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateSimplifiedCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $result = $handler->execute($this->command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws TransactionCreationException
     */
    public function it_should_throw_exception_when_previous_transaction_is_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->transactionRepositoryMock->method('findById')->willReturn(null);

        $handler = new PerformRocketgateSimplifiedCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws TransactionCreationException
     */
    public function it_should_throw_exception_when_transaction_does_not_have_a_pending_status(): void
    {
        $this->expectException(InvalidStatusException::class);

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);

        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ["request" => "json"],
                    'response' => ["reasonCode" => "0", "responseCode" => "0", "reasonDesc" => "Success"],
                    'reason'   => '111',
                    'code'     => '1',
                ],
                JSON_THROW_ON_ERROR
            ),
            new DateTimeImmutable()
        );
        $previousTransaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateSimplifiedCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_write_bi_event(): void
    {
        $this->biLoggerMock->expects(self::once())->method('write');

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $handler = new PerformRocketgateSimplifiedCompleteThreeDCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeService,
            $this->biLoggerMock,
            $this->declinedBillerResponseExtraDataRepository
        );

        $handler->execute($this->command);
    }
}
