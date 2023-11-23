<?php
declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingChargeService;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class PerformNetbillingCancelRebillCommandHandlerTest extends IntegrationTestCase
{
    use CreateTransactionDataForNetbilling;

    private $handler;

    private $chargeService;

    private $transactionRepositoryMock;

    private $biLoggerMock;

    /**
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     * @throws \ProBillerNG\Transaction\Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->chargeService = $this->createMock(NetbillingChargeService::class);

        $this->chargeService->method('suspendRebill')->willReturn(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                '{"request":{"memberId":"113998598886","accountId":"' . $_ENV['NETBILLING_ACCOUNT_ID'] . '","siteTag":"' . $_ENV['NETBILLING_SITE_TAG'] . '","controlKeyword":"' . $_ENV['NETBILLING_MERCHANT_PASSWORD'] . '"},"response":["STOPPED recurring billing"],"code":"0","reason":"STOPPED recurring billing"}',
                new \DateTimeImmutable()
            )
        );

        $previousTransaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();

        $this->transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $this->transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $this->biLoggerMock = $this->createMock(BILoggerService::class);

        $this->handler = \Mockery::mock(
            PerformNetbillingCancelRebillCommandHandler::class.'[getNetbillingBillerMemberIdFromTransaction]',
            array(new HttpCommandDTOAssembler(),$this->transactionRepositoryMock, $this->chargeService, $this->biLoggerMock)
        );

        $this->handler->shouldReceive('getNetbillingBillerMemberIdFromTransaction')->andReturn('12345');

    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided()
    {
        $command = $this->createPerformNetbillingCancelRebillCommand();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_missing_transaction_exception_when_id_is_not_provided()
    {
        $this->expectException(MissingTransactionInformationException::class);

        // Invalid command - no transaction id
        $command = $this->createPerformNetbillingCancelRebillCommand(
            [
                'transactionId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_exception_when_transaction_not_found()
    {
        $this->expectException(TransactionNotFoundException::class);

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn(null);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = \Mockery::mock(
            PerformNetbillingCancelRebillCommandHandler::class.'[getNetbillingBillerMemberIdFromTransaction]',
            array(new HttpCommandDTOAssembler(),$transactionRepositoryMock, $this->chargeService, $biLoggerMock)
        );

        $handler->shouldReceive('getNetbillingBillerMemberIdFromTransaction')->andReturn('12345');

        $command = $this->createPerformNetbillingCancelRebillCommand();

        $handler->execute($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_site_tag_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no siteTag
        $command = $this->createPerformNetbillingCancelRebillCommand(
            [
                'siteTag' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_accountId_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no siteTag
        $command = $this->createPerformNetbillingCancelRebillCommand(
            [
                'accountId' => ''
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_missing_merchant_exception_when_merchantPassword_is_not_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);

        // Invalid command - no siteTag
        $command = $this->createPerformNetbillingCancelRebillCommand(
            [
                'merchantPassword' => ''
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
    public function execute_with_declined_biller_response_should_return_status_aborted_response()
    {
        $chargeService = $this->createMock(NetbillingChargeService::class);

        $chargeService->method('suspendRebill')->willReturn(
            NetbillingBillerResponse::createAbortedResponse(
                new NetbillingServiceException()
            )
        );

        $previousTransaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = \Mockery::mock(
            PerformNetbillingCancelRebillCommandHandler::class.'[getNetbillingBillerMemberIdFromTransaction]',
            array(new HttpCommandDTOAssembler(),$transactionRepositoryMock, $chargeService, $biLoggerMock)
        );

        $handler->shouldReceive('getNetbillingBillerMemberIdFromTransaction')->andReturn('12345');

        $command = $this->createPerformNetbillingCancelRebillCommand();

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals('aborted', $result['status']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_transaction_exception_when_non_netbilling_transaction_is_provided()
    {
        $this->expectException(InvalidTransactionInformationException::class);

        $rocketgatePreviousTransaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($rocketgatePreviousTransaction);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $handler = \Mockery::mock(
            PerformNetbillingCancelRebillCommandHandler::class.'[getNetbillingBillerMemberIdFromTransaction]',
            array(new HttpCommandDTOAssembler(),$transactionRepositoryMock, $this->chargeService, $biLoggerMock)
        );

        $handler->shouldReceive('getNetbillingBillerMemberIdFromTransaction')->andReturn('12345');

        $command = $this->createPerformNetbillingCancelRebillCommand();

        $handler->execute($command);
    }
}
