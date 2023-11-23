<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Services;

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
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\IntegrationTestCase;

/**
 * Class PerformRocketgateSaleCommandHandlerTest
 * @package Tests\Integration\Application\Services
 */
class PerformRocketgateSaleCommandHandlerTest extends IntegrationTestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject|RedisRepository
     */
    private $redisRepoMock;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->chargeCreditCardHandlerMock = $this->createMock(ChargeThreeDService::class);
        $this->chargeCreditCardHandlerMock->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "0",
                            "responseCode"     => "0",
                            "reason_desc"      => "Success",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
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

        /** @var DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository */
        $declinedBillerResponseRepository = $this->createMock(
            DeclinedBillerResponseExtraDataRepository::class
        );

        $this->redisRepoMock = $this->createMock(RedisRepository::class);

        parent::setUp();

        $this->handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->chargeCreditCardHandlerMock,
            $biLoggerMock,
            $declinedBillerResponseRepository,
            $this->redisRepoMock
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
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws TransactionCreationException
     */
    public function successful_execute_minimum_details_command_should_return_dto()
    {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge(
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
     * @return void
     * @throws \Exception
     * @throws InvalidChargeInformationException
     */
    public function execute_should_throw_exception_if_given_amount_is_invalid()
    {
        $this->expectException(InvalidChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['amount' => 'invalid amount']);

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
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['amount' => -1]);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws MissingChargeInformationException
     */
    public function execute_should_throw_exception_if_given_amount_is_missing()
    {
        $this->expectException(MissingChargeInformationException::class);
        // Invalid command
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['amount' => null]);

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
        $command = new PerformRocketgateNewCreditCardSaleCommand(
            $this->faker->uuid,
            null,
            'USD',
            $this->createNewCreditCardCommandPayment(null),
            $this->createCommandBillerFields(null),
            null
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function execute_with_aborted_biller_response_should_return_status_aborted_response()
    {
        /** @var ChargeThreeDService $chargeCreditCardHandlerMock */
        $rocketgateChargeServiceMock = $this->createMock(RocketgateChargeService::class);
        $rocketgateChargeServiceMock->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            new ChargeThreeDService($rocketgateChargeServiceMock),
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals('aborted', $result['status']);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_receive_threed_auth_for_a_eligible_card(): array
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => true],
                        'response' => [
                            "reasonCode"   => "0",
                            "responseCode" => "0",
                            "reasonDesc"   => "Success",
                            "acsURL"       => "url",
                            "PAREQ"        => "pareq"
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '202',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['useThreeD' => true]);

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertArrayHasKey('pareq', $result);

        return $result;
    }

    /**
     * @test
     * @param array $result result
     * @return void
     * @depends it_should_receive_threed_auth_for_a_eligible_card
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_return_pareq_result_when_3ds_auth_is_required(array $result)
    {
        $this->assertArrayHasKey('pareq', $result);
    }

    /**
     * @test
     * @param array $result result
     * @return void
     * @depends it_should_receive_threed_auth_for_a_eligible_card
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_return_acs_url_when_3ds_auth_is_required(array $result)
    {
        $this->assertArrayHasKey('acs', $result);
    }

    /**
     * @test
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function it_should_retry_transaction_without_threed_for_a_not_eligible_card()
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $chargeService->expects($this->exactly(2))->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => true, "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "0",
                            "responseCode"     => "0",
                            "reason_desc"      => "Success",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_NOT_ENROLLED,
                        'code'     => '2'
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['useThreeD' => true]);
        $handler->execute($command);
    }

    /**
     * @test
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function it_should_charge_transaction_without_threed_for_any_eligible_card()
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "0",
                            "responseCode"     => "0",
                            "reason_desc"      => "Success",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
                        ],
                        'reason'   => '111',
                        'code'     => '1'
                    ],

                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();
        $handler->execute($command);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_receive_threed_version_two_for_a_eligible_card(): array
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => true],
                        'response' => [
                            "reasonCode"                      => "0",
                            "responseCode"                    => "0",
                            "reasonDesc"                      => "Success",
                            "_3DSECURE_DEVICE_COLLECTION_JWT" => "device_jwt",
                            "_3DSECURE_DEVICE_COLLECTION_URL" => "device_url"
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['useThreeD' => true]);

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertArrayHasKey('threeD', $result);

        return $result['threeD'];
    }

    /**
     * @test
     * @param array $result result
     * @return void
     * @depends it_should_receive_threed_version_two_for_a_eligible_card
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_return_device_collection_url_result_when_3ds_version_two_is_required(array $result)
    {
        $this->assertArrayHasKey('deviceCollectionUrl', $result);
    }

    /**
     * @test
     * @param array $result result
     * @return void
     * @depends it_should_receive_threed_version_two_for_a_eligible_card
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_return_device_collection_jwt_when_3ds_version_two_is_required(array $result)
    {
        $this->assertArrayHasKey('deviceCollectionJWT', $result);
    }

    /**
     * @test
     * @return array
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws TransactionCreationException
     * @throws JsonException
     * @throws Exception
     * @throws InvalidPayloadException
     */
    public function decline_transaction_should_have_default_error_classification_when_no_translation_found(): array
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "104",
                            "responseCode"     => "104",
                            "reason_desc"      => "Decline",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
                        ],
                        'reason'   => 'TEST DECLINED',
                        'code'     => '104',
                    ],

                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->createMock(FirestoreTransactionRepository::class),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

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
    public function decline_sale_response_should_contain_error_classification(array $responseContent): array
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
        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE, $errorClassification['groupDecline']
        );
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
        $this->assertEquals(ErrorClassification::DEFAULT_ERROR_TYPE, $errorClassification['errorType']);
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
        $this->assertEquals(ErrorClassification::DEFAULT_GROUP_MESSAGE, $errorClassification['groupMessage']);
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
        $this->assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION, $errorClassification['recommendedAction']
        );
    }

    /**
     * @test
     * @depends decline_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return array
     */
    public function error_classification_should_contain_mapping_criteria(array $errorClassification): array
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
     * @return array
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function aborted_transaction_should_have_error_classification_in_the_response(): array
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => $this->getRGAbortedBillerResponse(),
                        'reason'   => '311',
                        'code'     => '3',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->createMock(FirestoreTransactionRepository::class),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $result = $handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @depends aborted_transaction_should_have_error_classification_in_the_response
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function aborted_sale_response_should_contain_status_and_code_for_abort_sale(array $responseContent): void
    {
        $this->assertEquals(Aborted::NAME, $responseContent['status']);
        $this->assertEquals(RocketgateErrorCodes::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR,
            $responseContent['code']
        );
    }

    /**
     * @test
     * @depends aborted_transaction_should_have_error_classification_in_the_response
     *
     * @param array $responseContent Response
     *
     * @return array
     */
    public function aborted_sale_response_should_contain_error_classification(array $responseContent): array
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
        return $responseContent['errorClassification'];
    }

    /**
     * @test
     * @depends aborted_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_mappingCriteria(array $errorClassification)
    {
        $this->assertArrayHasKey('mappingCriteria', $errorClassification);
    }

    /**
     * @test
     * @depends aborted_sale_response_should_contain_error_classification
     *
     * @param array $errorClassification Error classification
     *
     * @return void
     */
    public function error_classification_should_contain_error_classification_required_fields(array $errorClassification)
    {
        $this->assertArrayHasKey('groupDecline', $errorClassification);
        $this->assertArrayHasKey('errorType', $errorClassification);
        $this->assertArrayHasKey('groupMessage', $errorClassification);
        $this->assertArrayHasKey('recommendedAction', $errorClassification);
    }


    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws TransactionCreationException
     */
    public function it_should_do_card_upload_and_return_declined_NSF_transaction_containing_error_classification(): void
    {
        /** @var ChargeThreeDService $chargeCreditCardHandlerMock */
        $rocketgateChargeServiceMock = $this->createMock(RocketgateChargeService::class);

        $rocketgateChargeServiceMock->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "105",
                            "responseCode"     => "1",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
                        ],
                        'reason'   => '105',
                        'code'     => '105',
                    ],

                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $rocketgateChargeServiceMock->method('cardUpload')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "merchantID" => "123456789"],
                        'response' => [
                            "reasonCode"       => "0",
                            "responseCode"     => "0",
                            'merchantAccount'  => '10',
                            'bankResponseCode' => '91'
                        ],
                        'reason'   => '0',
                        'code'     => '0',
                    ],

                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->createMock(FirestoreTransactionRepository::class),
            new ChargeThreeDService($rocketgateChargeServiceMock),
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $this->redisRepoMock
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $result = $handler->execute($command)->jsonSerialize();

        $this->assertEquals(Declined::NAME, $result['status']);
        $this->assertEquals(RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT, $result['code']);
        $this->assertArrayHasKey('errorClassification', $result);
    }

    /**
     * @test
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function it_should_store_cvv_when_auth_is_required_and_transaction_status_is_pending(): void
    {
        $chargeService = $this->createMock(ChargeService::class);

        $chargeService->expects($this->once())->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => true],
                        'response' => [
                            "reasonCode"   => "0",
                            "responseCode" => "0",
                            "reasonDesc"   => "Success",
                            "acsURL"       => "url",
                            "PAREQ"        => "pareq"
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '202',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $chargeCreditCardHandlerMock = new ChargeThreeDService($chargeService);

        $redis = new RedisRepository();

        $handler = new PerformRocketgateNewCreditCardSaleCommandHandler(
            new HttpCommandDTOAssembler(),
            new FirestoreTransactionRepository(
                new FirestoreClient(['projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')]),
                app()->make(FirestoreSerializer::class)
            ),
            $chargeCreditCardHandlerMock,
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class),
            $redis
        );

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['useThreeD' => true]);

        $result = $handler->execute($command)->jsonSerialize();

        // retrieve CVV stored during the purchase process
        $retrieveStoredCVVFromRedis = $redis->retrieveCvv(
            $result['transactionId'], RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals($command->payment()->information()->cvv(), $retrieveStoredCVVFromRedis);
    }
}