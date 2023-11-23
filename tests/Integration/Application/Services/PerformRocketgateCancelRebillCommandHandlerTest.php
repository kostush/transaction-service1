<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Service;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use Tests\IntegrationTestCase;

class PerformRocketgateCancelRebillCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var PerformRocketgateCancelRebillCommandHandler
     */
    private $handler;

    /**
     * @var RocketgateChargeService
     */
    private $chargeService;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->chargeService = $this->createMock(RocketgateChargeService::class);
        $this->chargeService->method('suspendRebill')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => ["reasonCode"=>"0","responseCode"=>"0","reasonDesc"=>"Success"],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $this->handler = new PerformRocketgateCancelRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->chargeService,
            $biLoggerMock
        );
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided()
    {
        $command = $this->createPerformRocketGateCancelRebillCommand();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_transaction_exception_when_id_is_not_provided()
    {
        $this->expectException(MissingTransactionInformationException::class);

        // Invalid command - no transaction id
        $command = $this->createPerformRocketGateCancelRebillCommand(
            [
                'transactionId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_transaction_not_found()
    {
        $this->expectException(TransactionNotFoundException::class);

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn(null);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = new PerformRocketgateCancelRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->chargeService,
            $biLoggerMock
        );
        $command = $this->createPerformRocketGateCancelRebillCommand();

        $handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_merchant_id_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketGateCancelRebillCommand(
            [
                'merchantId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_merchant_password_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketGateCancelRebillCommand(
            [
                'merchantPassword' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_merchant_customer_id_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketGateCancelRebillCommand(
            [
                'merchantCustomerId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_merchant_invoice_id_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketGateCancelRebillCommand(
            [
                'merchantInvoiceId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function execute_with_declined_biller_response_should_return_status_declined_response()
    {
        $chargeService = $this->createMock(RocketgateChargeService::class);
        $chargeService->method('suspendRebill')->willReturn(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = new PerformRocketgateCancelRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->chargeService,
            $biLoggerMock
        );

        $command = $this->createPerformRocketGateCancelRebillCommand();

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals('declined', $result['status']);
    }
}
