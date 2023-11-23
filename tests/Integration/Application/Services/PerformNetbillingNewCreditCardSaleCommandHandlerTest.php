<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingChargeService;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class PerformNetbillingNewCreditCardSaleCommandHandlerTest extends IntegrationTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @var PerformNetbillingNewCreditCardSaleCommandHandler
     */
    private $handler;

    /**
     * @var NetbillingChargeService|MockObject
     */
    private $chargeCreditCardHandlerMock;

    /**
     * @var FirestoreTransactionRepository|MockObject
     */
    private $transactionRepositoryMock;

    /**
     * @var BILoggerService|MockObject
     */
    private $biLoggerMock;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->chargeCreditCardHandlerMock = $this->createMock(NetbillingChargeService::class);
        $this->chargeCreditCardHandlerMock->method('chargeNewCreditCard')->willReturn(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => $this->getBillerRequest(),
                        'response' => $this->getBillerResponse(),
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $this->transactionRepositoryMock = $this->createMock(FirestoreTransactionRepository::class);

        /** @var BILoggerService $biLoggerMock */
        $this->biLoggerMock = $this->createMock(BILoggerService::class);

        /** @var DeclinedBillerResponseExtraDataRepository $declinedBillerResponseRepository */
        $declinedBillerResponseRepository = $this->createMock(
            DeclinedBillerResponseExtraDataRepository::class
        );

        $this->handler = new PerformNetbillingNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $this->chargeCreditCardHandlerMock,
            $this->biLoggerMock,
            $declinedBillerResponseRepository
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function successful_execute_full_details_command_should_return_dto()
    {
        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws InvalidChargeInformationException
     */
    public function execute_should_throw_exception_if_given_amount_is_invalid()
    {
        $this->expectException(InvalidChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill(['amount' => 'amount']);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws InvalidChargeInformationException
     */
    public function execute_should_throw_exception_if_given_amount_is_negative()
    {
        $this->expectException(InvalidChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill(['amount' => -1]);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws MissingChargeInformationException
     */
    public function execute_should_throw_exception_if_given_amount_is_given_null()
    {
        $this->expectException(MissingChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill(['amount' => null]);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws MissingChargeInformationException
     */
    public function execute_should_throw_exception_if_no_amount_is_given()
    {
        $this->expectException(MissingChargeInformationException::class);
        // Invalid command
        $command = new PerformNetbillingNewCreditCardSaleCommand(
            $this->faker->uuid,
            null,
            'USD',
            $this->createNewCreditCardCommandPayment(null),
            $this->createNetbillingBillerFields(null),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws MissingChargeInformationException
     */
    public function execute_should_throw_exception_if_no_member_information_is_given()
    {
        $this->expectException(InvalidPaymentInformationException::class);
        // Invalid command
        $command = new PerformNetbillingNewCreditCardSaleCommand(
            $this->faker->uuid,
            20,
            'USD',
            $this->createNewCreditCardCommandPaymentWithNetbilling(null, false),
            $this->createNetbillingBillerFields(null),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function decline_transaction_should_return_error_classification_based_on_biller_response() : array
    {
        $chargeCreditCardHandlerMock = $this->createMock(NetbillingChargeService::class);
        $chargeCreditCardHandlerMock->method('chargeNewCreditCard')->willReturn(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => $this->getBillerRequest(),
                        'response' => $this->getNBDeclinedBillerResponse(),
                        'reason'   => 'TEST DECLINED',
                        'code'     => '100',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $handler = new PerformNetbillingNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->transactionRepositoryMock,
            $chargeCreditCardHandlerMock,
            $this->biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill();

        /** @var TransactionCommandHttpDTO $result */
        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @depends decline_transaction_should_return_error_classification_based_on_biller_response
     *
     * @param array $responseContent Response
     *
     * @return array
     */
    public function decline_sale_response_should_contain_error_classification(array $responseContent) : array
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
        return $responseContent['errorClassification'];
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_groupDecline(array $errorClassification)
    {
        $this->assertArrayHasKey('groupDecline', $errorClassification);
//        $this->assertEquals(100, $errorClassification['groupDecline']);
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_errorType(array $errorClassification)
    {
        $this->assertArrayHasKey('errorType', $errorClassification);
        $this->assertEquals('Error', $errorClassification['errorType']);
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_groupMessage(array $errorClassification)
    {
        $this->assertArrayHasKey('groupMessage', $errorClassification);
//        $this->assertEquals('Declined Netbilling', $errorClassification['groupMessage']);
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_recommendedAction(array $errorClassification)
    {
        $this->assertArrayHasKey('recommendedAction', $errorClassification);
//        $this->assertEquals('Retry maybe you succeed.', $errorClassification['recommendedAction']);
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return array
     */
    public function error_classification_should_contain_mapping_criteria(array $errorClassification) : array
    {
        $this->assertArrayHasKey('mappingCriteria', $errorClassification);
        return $errorClassification['mappingCriteria'];
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_processor(array $mappingCriteria)
    {
        $this->assertArrayHasKey('processor', $mappingCriteria);
        $this->assertEquals('TEST', $mappingCriteria['processor']);
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_authMessage(array $mappingCriteria)
    {
        $this->assertArrayHasKey('authMessage', $mappingCriteria);
        $this->assertEquals('TEST DECLINED', $mappingCriteria['authMessage']);
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_billerName(array $mappingCriteria)
    {
        $this->assertArrayHasKey('billerName', $mappingCriteria);
        $this->assertEquals(BillerSettings::NETBILLING, $mappingCriteria['billerName']);
    }
}
