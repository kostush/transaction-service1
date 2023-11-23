<?php

declare(strict_types=1);

namespace Tests\Integration\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\IntegrationTestCase;

/**
 * Class ChargeThreeDServiceTest
 * @package Tests\Integration\Domain\Services
 */
class ChargeThreeDServiceTest extends IntegrationTestCase
{
    /**
     * @var ChargeThreeDService
     */
    private $service;

    /**
     * @var MockObject
     */
    private $chargeService;

    /**
     * Setup test
     * @return void
     */
    public function setUp(): void
    {
        $this->chargeService = $this->createMock(ChargeService::class);

        $this->service = new ChargeThreeDService(
            $this->chargeService
        );

        parent::setUp();
    }

    /**
     * @test
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_a_credit_card_biller_result_when_biller_response_is_success()
    {
        $this->chargeService
            ->method('chargeNewCreditCard')->willReturn(
                RocketgateCreditCardBillerResponse::create(
                    new \DateTimeImmutable(),
                    json_encode(
                        [
                            'request'  => ['request' => 'json'],
                            'response' => [
                                'reason_code'   => '0',
                                'response_code' => '0',
                                'reason_desc'   => 'Success'
                            ],
                            'reason'   => '0',
                            'code'     => '0',
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    new \DateTimeImmutable()
                )
            );

        /** @var RocketgateCreditCardBillerResponse $billerResult */
        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );

        $this->assertInstanceOf(RocketgateCreditCardBillerResponse::class, $billerResult);

        return $billerResult;
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_when_biller_response_is_success
     * @return void
     */
    public function new_credit_card_biller_result_should_have_approved_reason(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_APPROVED, $billerResult->result());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_when_biller_response_is_success
     * @return void
     */
    public function new_credit_card_biller_result_should_have_code(RocketgateCreditCardBillerResponse $billerResult)
    {
        $this->assertNotNull($billerResult->code());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_when_biller_response_is_success
     * @return void
     */
    public function new_credit_card_biller_result_should_have_reason(RocketgateCreditCardBillerResponse $billerResult)
    {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_when_biller_response_is_success
     * @return void
     */
    public function new_credit_card_biller_result_should_have_request_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->requestPayload());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_when_biller_response_is_success
     * @return void
     */
    public function new_credit_card_biller_result_should_have_response_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->responsePayload());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_credit_card_biller_result_with_declined_status_when_biller_response_is_declined()
    {
        $this->chargeService
            ->method('chargeNewCreditCard')->willReturn(
                RocketgateCreditCardBillerResponse::create(
                    new \DateTimeImmutable(),
                    json_encode(
                        [
                            'request'  => ['request' => 'json'],
                            'response' => [
                                'reason_code'   => '0',
                                'response_code' => '0',
                                'reason_desc'   => 'Success'
                            ],
                            'reason'   => '111',
                            'code'     => '1',
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    new \DateTimeImmutable()
                )
            );

        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );

        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @covers \ProBillerNG\Transaction\Domain\Services\ChargeThreeDService::chargeNewCreditCard
     *  (when $billerResponse->shouldRetryWithoutThreeD())
     */
    public function it_should_return_a_credit_card_biller_result_when_transaction_should_be_retried_without_ThreeD(): void
    {
        $this->chargeService
            ->method('chargeNewCreditCard')->willReturn(
                RocketgateCreditCardBillerResponse::create(
                    new \DateTimeImmutable(),
                    json_encode(
                        [
                            'request'  => ['request' => 'json'],
                            'response' => [
                                'reasonCode'   => '223',
                                'responseCode' => '2'
                            ],
                            'reason'   => '223',
                            'code'     => '2',
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    new \DateTimeImmutable()
                )
            );

        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );

        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
        $this->assertEquals(RocketgateErrorCodes::RG_CODE_3DS_BYPASS, $billerResult->code());
        $this->assertEquals(RocketgateErrorCodes::getMessage((int) $billerResult->code()), $billerResult->reason());
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_one(): array
    {
        $this->chargeService->expects($this->exactly(2))->method('chargeNewCreditCard')->willReturnOnConsecutiveCalls(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                            'responseCode' => '2'
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => 'TRUE'
                        ],
                        'response' => [
                            'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                            'responseCode' => '2',
                            'PAREQ'        => 'SimulatedPAREQ1000175E0A055C9',
                            'acsURL'       => $this->faker->url
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
        );

        $transaction  = $this->createPendingTransactionWithRebillForNewCreditCard();
        $billerResult = $this->service->chargeNewCreditCard($transaction);

        $this->assertTrue($transaction->with3D());

        return [
            'transaction'  => $transaction,
            'billerResult' => $billerResult
        ];
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_one
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_three_d_version_one_set_on_transaction(array $transactionAndBillerResult): void
    {
        $this->assertSame(1, $transactionAndBillerResult['transaction']->threedsVersion());
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_one
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_three_ds_one_authentication_code_on_biller_result(
        array $transactionAndBillerResult
    ): void {
        $this->assertEquals(
            RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
            $transactionAndBillerResult['billerResult']->code()
        );
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_one
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_a_pending_charge_on_biller_result(array $transactionAndBillerResult): void
    {
        $this->assertEquals(
            RocketgateBillerResponse::CHARGE_RESULT_PENDING,
            $transactionAndBillerResult['billerResult']->result()
        );
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_two(): array
    {
        $this->chargeService->expects($this->exactly(2))->method('chargeNewCreditCard')->willReturnOnConsecutiveCalls(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                            'responseCode' => '2'
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => 'TRUE'
                        ],
                        'response' => [
                            'reasonCode'                      => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                            'responseCode'                    => '2',
                            '_3DSECURE_DEVICE_COLLECTION_JWT' => 'jwt',
                            '_3DSECURE_DEVICE_COLLECTION_URL' => $this->faker->url
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
        );

        $transaction  = $this->createPendingTransactionWithRebillForNewCreditCard();
        $billerResult = $this->service->chargeNewCreditCard($transaction);

        $this->assertTrue($transaction->with3D());

        return [
            'transaction'  => $transaction,
            'billerResult' => $billerResult
        ];
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_two
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_ThreeD_version_two_set_on_transaction(array $transactionAndBillerResult): void
    {
        $this->assertSame(2, $transactionAndBillerResult['transaction']->threedsVersion());
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_version_two
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_three_ds_two_initiation_code_on_biller_result(
        array $transactionAndBillerResult
    ): void {
        $this->assertEquals(
            RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
            $transactionAndBillerResult['billerResult']->code()
        );
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_because_3ds_sca_required(): array
    {
        $this->chargeService->expects($this->exactly(2))->method('chargeNewCreditCard')->willReturnOnConsecutiveCalls(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                            'responseCode' => '2'
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => 'TRUE'
                        ],
                        'response' => [
                            'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                            'responseCode' => '2',
                            'PAREQ'        => 'SimulatedPAREQ1000175E0A055C9',
                            'acsURL'       => $this->faker->url
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
        );

        $transaction  = $this->createPendingTransactionWithRebillForNewCreditCard();
        $billerResult = $this->service->chargeNewCreditCard($transaction);

        $this->assertTrue($transaction->with3D());

        return [
            'transaction'  => $transaction,
            'billerResult' => $billerResult
        ];
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_because_3ds_sca_required
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_three_d_version_one_set_on_transaction_because_3ds_sca_required(array $transactionAndBillerResult): void
    {
        $this->assertSame(1, $transactionAndBillerResult['transaction']->threedsVersion());
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_because_3ds_sca_required
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_three_ds_one_authentication_code_on_biller_result_because_3ds_sca_required(
        array $transactionAndBillerResult
    ): void {
        $this->assertEquals(
            RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
            $transactionAndBillerResult['billerResult']->code()
        );
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_with_three_d_when_transaction_should_be_retried_with_ThreeD_because_3ds_sca_required
     * @param array $transactionAndBillerResult Transaction and biller result
     * @return void
     */
    public function it_should_have_a_pending_charge_on_biller_result_because_3ds_sca_required(array $transactionAndBillerResult): void
    {
        $this->assertEquals(
            RocketgateBillerResponse::CHARGE_RESULT_PENDING,
            $transactionAndBillerResult['billerResult']->result()
        );
    }
}
