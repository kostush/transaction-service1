<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Service;

use DateTimeImmutable;
use Google\Cloud\Firestore\FirestoreClient;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateExistingCreditCardBillerFields;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use Tests\IntegrationTestCase;

class PerformRocketgateExistingCreditCardSaleCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var PerformRocketgateNewCreditCardSaleCommandHandler
     */
    private $handler;

    /**
     * @var RocketgateChargeService|MockObject
     */
    private $chargeCreditCardHandlerMock;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->chargeCreditCardHandlerMock = $this->createMock(ChargeThreeDService::class);
        $this->chargeCreditCardHandlerMock->method('chargeExistingCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode" => "0", "responseCode" => "0", "reason_desc" => "Success",
                            'merchantAccount' => '10', 'bankResponseCode' => '91'
                        ],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );
        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $transactionRepositoryMock = $this->createMock(FirestoreTransactionRepository::class);

        /** @var BILoggerService $biLoggerMock */
        $biLoggerMock = $this->createMock(BILoggerService::class);

        /** @var DeclinedBillerResponseExtraDataRepository $declinedBillerResponseRepository */
        $declinedBillerResponseRepository = $this->createMock(
            DeclinedBillerResponseExtraDataRepository::class
        );
        parent::setUp();

        $this->handler = new PerformRocketgateExistingCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->chargeCreditCardHandlerMock,
            $biLoggerMock,
            $declinedBillerResponseRepository
        );
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function successful_execute_full_details_command_should_return_dto()
    {
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function successful_execute_minimum_details_command_should_return_dto()
    {
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandSingleCharge(
            [
                'member'             => null,
                'merchantAccount'    => null,
                'merchantSiteId'     => null,
                'merchantProductId'  => null,
                'merchantDescriptor' => null,
                'merchantCustomerId' => null,
                'merchantInvoiceId'  => null,
                'ipAddress'          => null
            ]
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function execute_should_throw_exception_if_given_amount_is_invalid()
    {
        $this->expectException(InvalidChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill(['amount' => 'invalid amount']);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function execute_should_throw_exception_if_given_amount_is_negative()
    {
        $this->expectException(InvalidChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill(['amount' => -1]);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws MissingChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function execute_should_throw_exception_if_given_amount_is_missing()
    {
        $this->expectException(MissingChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill(['amount' => null]);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws MissingChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function execute_should_throw_exception_if_no_amount_is_given()
    {
        $this->expectException(MissingChargeInformationException::class);
        // Invalid command
        $command = new PerformRocketgateExistingCreditCardSaleCommand(
            $this->faker->uuid,
            null,
            'USD',
            $this->createExistingCreditCardCommandPayment(null),
            $this->createCommandBillerFields(null),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function execute_with_aborted_biller_response_should_return_status_aborted_response()
    {
        /** @var RocketgateChargeService $chargeCreditCardHandlerMock */
        $chargeCreditCardHandlerMock = $this->createMock(ChargeThreeDService::class);
        $chargeCreditCardHandlerMock->method('chargeExistingCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );

        $handler = new PerformRocketgateExistingCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill();

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals('aborted', $result['status']);
    }

    /**
     * @test
     * @return void
     *@throws MissingChargeInformationException
     * @throws TransactionCreationException
     * @throws Exception
     * @throws InvalidPayloadException
     * @throws MissingCreditCardInformationException
     * @throws InvalidChargeInformationException
     */
    public function execute_whithout_merchant_customer_id_should_throw_exception(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        // Invalid command
        $command = new PerformRocketgateExistingCreditCardSaleCommand(
            $this->faker->uuid,
            null,
            'USD',
            $this->createExistingCreditCardCommandPayment(null),
            new RocketGateExistingCreditCardBillerFields(
                '123123',
                'passs',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     *@throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     * @throws TransactionCreationException
     * @throws Exception
     * @throws InvalidPayloadException
     * @throws MissingCreditCardInformationException
     * @throws InvalidChargeInformationException
     */
    public function execute_without_card_hash_should_throw_exception(): void
    {
        $this->expectException(MissingCreditCardInformationException::class);
        // Invalid command
        $command = new PerformRocketgateExistingCreditCardSaleCommand(
            $this->faker->uuid,
            null,
            'USD',
            new Payment(
                'cc',
                new ExistingCreditCardInformation('')
            ),
            new RocketGateExistingCreditCardBillerFields(
                '123123',
                'passs',
                null,
                null,
                null,
                null,
                '12345',
                null,
                null
            ),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return array
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionCreationException
     * @throws JsonException
     * @throws Exception
     * @throws InvalidPayloadException
     */
    public function decline_transaction_should_have_default_error_classification_when_no_translation_found() : array
    {
        $chargeCreditCardHandlerMock = $this->createMock(ChargeThreeDService::class);
        $chargeCreditCardHandlerMock->method('chargeExistingCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => $_ENV['ROCKETGATE_MERCHANT_ID_3']],
                        'response' => [
                            "reasonCode" => "104", "responseCode" => "104", "reason_desc" => "Decline",
                            'merchantAccount' => '114', 'bankResponseCode' => '3013'
                        ],
                        'reason' => 'TEST DECLINED',
                        'code' => '104',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateExistingCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->createMock(FirestoreTransactionRepository::class),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->app->make(DeclinedBillerResponseExtraDataRepository::class)
        );


        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill();

        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @depends decline_transaction_should_have_default_error_classification_when_no_translation_found
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
//        $this->assertNotEquals(
//            ErrorClassification::DEFAULT_GROUP_DECLINE, $errorClassification['groupDecline']
//        );
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
//        $this->assertNotEquals(ErrorClassification::DEFAULT_ERROR_TYPE, $errorClassification['errorType']);
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
//        $this->assertNotEquals(
//            ErrorClassification::DEFAULT_GROUP_MESSAGE, $errorClassification['groupMessage']
//        );
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
//        $this->assertNotEquals(
//            ErrorClassification::DEFAULT_RECOMMENDED_ACTION, $errorClassification['recommendedAction']
//        );
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
    public function mapping_criteria_should_contain_merchantId(array $mappingCriteria)
    {
        $this->assertArrayHasKey('merchantId', $mappingCriteria);
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_reasonCode(array $mappingCriteria)
    {
        $this->assertArrayHasKey('reasonCode', $mappingCriteria);
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_merchantAccount(array $mappingCriteria)
    {
        $this->assertArrayHasKey('merchantAccount', $mappingCriteria);
    }

    /**
     * @test
     * @depends error_classification_should_contain_mapping_criteria
     *
     * @param array $mappingCriteria Response
     *
     * @return void
     */
    public function mapping_criteria_should_contain_bankResponseCode(array $mappingCriteria)
    {
        $this->assertArrayHasKey('bankResponseCode', $mappingCriteria);
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
        $this->assertEquals(BillerSettings::ROCKETGATE, $mappingCriteria['billerName']);
    }

    /**
     * @test
     * @throws InvalidArgumentException
     * @throws \Exception
     * @return array
     */
    public function aborted_biller_response_from_CB_fallback_should_return_default_error_classification() : array
    {
        /** @var RocketgateChargeService $chargeCreditCardHandlerMock */
        $chargeCreditCardHandlerMock = $this->createMock(ChargeThreeDService::class);
        $chargeCreditCardHandlerMock->method('chargeExistingCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );

        $handler = new PerformRocketgateExistingCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->createMock(FirestoreTransactionRepository::class),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill();

        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
        $this->assertEquals(Aborted::NAME, $result->jsonSerialize()['status']);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @depends aborted_biller_response_from_CB_fallback_should_return_default_error_classification
     *
     * @param array $responseContent Response
     *
     * @return array
     */
    public function aborted_transaction_response_should_contain_default_error_classification(array $responseContent): array
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
        return $responseContent['errorClassification'];
    }

    /**
     * @test
     * @depends aborted_transaction_response_should_contain_default_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function aborted_error_classification_should_contain_groupDecline(array $errorClassification)
    {
        $this->assertArrayHasKey('groupDecline', $errorClassification);
        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE, $errorClassification['groupDecline']
        );
    }

    /**
     * @test
     * @depends aborted_transaction_response_should_contain_default_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function aborted_error_classification_should_contain_errorType(array $errorClassification)
    {
        $this->assertArrayHasKey('errorType', $errorClassification);
        $this->assertEquals(ErrorClassification::DEFAULT_ERROR_TYPE, $errorClassification['errorType']);
    }

    /**
     * @test
     * @depends aborted_transaction_response_should_contain_default_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function aborted_error_classification_should_contain_groupMessage(array $errorClassification)
    {
        $this->assertArrayHasKey('groupMessage', $errorClassification);
        $this->assertEquals(ErrorClassification::DEFAULT_GROUP_MESSAGE, $errorClassification['groupMessage']);
    }

    /**
     * @test
     * @depends aborted_transaction_response_should_contain_default_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function aborted_error_classification_should_contain_recommendedAction(array $errorClassification)
    {
        $this->assertArrayHasKey('recommendedAction', $errorClassification);
        $this->assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION, $errorClassification['recommendedAction']
        );
    }
}
