<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Service;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillTranslatingService;
use Tests\IntegrationTestCase;

class PerformRocketgateUpdateRebillCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var PerformRocketgateUpdateRebillCommandHandler
     */
    private $handler;

    /**
     * @var RocketgateUpdateRebillTranslatingService
     */
    private $updateRebillService;

    const MERCHANT_CUSTOMER_ID = '700c0c07-6dc7-4116-b921-3353ae1d700';

    const MERCHANT_INVOICE_ID = '555866b7-5dea66d06a1355.73747555';

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->updateRebillService = $this->createMock(RocketgateUpdateRebillTranslatingService::class);
        $this->updateRebillService->method('update')->willReturn(
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

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard(
            [
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
            ]
        );

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $this->handler = new PerformRocketgateUpdateRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->updateRebillService,
            $biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
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
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
            ]
        );

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
        $command = $this->createPerformRocketgateUpdateRebillCommand(
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

        $handler = new PerformRocketgateUpdateRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->updateRebillService,
            $biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );
        $command = $this->createPerformRocketgateUpdateRebillCommand();

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
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantId' => '',
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
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
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantPassword' => '',
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
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
    public function it_should_throw_invalid_merchant_exception_when_merchant_customer_id_is_not_provided()
    {
        $this->expectException(InvalidMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketgateUpdateRebillCommand(
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
    public function it_should_throw_invalid_merchant_exception_when_merchant_invoice_id_is_not_provided()
    {
        $this->expectException(InvalidMerchantInformationException::class);

        // Invalid command - no merchant id
        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantInvoiceId' => ''
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
    public function it_should_throw_charge_exception_when_rebill_information_is_not_provided()
    {
        $this->expectException(InvalidChargeInformationException::class);

        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID,
                'rebill' => []
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
    public function it_should_throw_charge_exception_when_amount_with_invalid_payment_information_is_provided()
    {
        $this->expectException(InvalidCreditCardNumberException::class);

        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID,
                'amount'  => 20,
                'payment' => [
                    'method'      => 'cc',
                    'information' => [
                        'number' => 'invalid'
                    ]
                ]
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
    public function it_should_return_status_declined_when_declined_biller_response_is_provided()
    {
        $chargeService = $this->createMock(RocketgateUpdateRebillTranslatingService::class);
        $chargeService->method('update')->willReturn(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );

        $previousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard(
            [
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
            ]
        );

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = new PerformRocketgateUpdateRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->updateRebillService,
            $biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        $command = $this->createPerformRocketgateUpdateRebillCommand(
            [
                'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
                'merchantInvoiceId'  => self::MERCHANT_INVOICE_ID
            ]
        );

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals('declined', $result['status']);
    }
}
