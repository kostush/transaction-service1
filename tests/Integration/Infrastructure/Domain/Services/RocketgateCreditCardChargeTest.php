<?php

declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentTypeForBillerException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCompleteThreeDCreditCardAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateNewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardTranslationService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateOtherPaymentTypeChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\OtherPaymentTypeChargeClient;
use Tests\IntegrationTestCase;

class RocketgateCreditCardChargeTest extends IntegrationTestCase
{
    /**
     * @var RocketgateChargeService
     */
    private $service;

    /**
     * @var MockObject
     */
    private $rocketgateClient;

    /**
     * Setup test
     * @return void
     */
    public function setUp(): void
    {
        $this->rocketgateClient = $this->createMock(ChargeClient::class);

        /** @var RocketgateChargeService $chargeCreditCardHandlerMock */
        $this->service = new RocketgateChargeService(
            new RocketgateCreditCardTranslationService(
                new RocketgateExistingCreditCardChargeAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                ),
                new RocketgateNewCreditCardChargeAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                ),
                new RocketgateSuspendRebillAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                ),
                new RocketgateCompleteThreeDCreditCardAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                ),
                new RocketgateSimplifiedCompleteThreeDAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                ),
                new RocketgateCardUploadAdapter(
                    $this->rocketgateClient,
                    new RocketgateCreditCardChargeTranslator()
                )
            ),
            new RocketgateOtherPaymentTypeChargeAdapter($this->createMock(OtherPaymentTypeChargeClient::class))
        );

        parent::setUp();
    }

    /**
     * @test
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     * @throws UnknownPaymentTypeForBillerException|\JsonException
     */
    public function it_should_return_a_credit_card_biller_result_when_biller_response_is_success()
    {
        $this->rocketgateClient
            ->method('chargeNewCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => '{"reason_code":"0","response_code":"0","reason_desc":"Success"}',
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
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
        $this->assertEquals(RocketGateBillerResponse::CHARGE_RESULT_APPROVED, $billerResult->result());
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
     * @throws \Exception
     * @return void
     */
    public function it_should_return_a_credit_card_biller_result_with_declined_status_when_biller_response_is_declined()
    {
        $this->rocketgateClient
            ->method('chargeNewCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => '{"reason_code":"0","response_code":"0","reason_desc":"Success"}',
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );

        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }

    /**
     * @test
     * @throws \Exception
     * @return RocketgateCreditCardBillerResponse
     */
    public function it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success()
    {
        $this->rocketgateClient
            ->method('chargeExistingCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => '{"reason_code":"0","response_code":"0","reason_desc":"Success"}',
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        /** @var RocketgateCreditCardBillerResponse $billerResult */
        $billerResult = $this->service->chargeExistingCreditCard(
            $this->createPendingTransactionWithRebillForExistingCreditCard()
        );

        $this->assertInstanceOf(RocketgateCreditCardBillerResponse::class, $billerResult);

        return $billerResult;
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success
     * @return void
     */
    public function existing_credit_card_biller_result_should_have_approved_reason(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_APPROVED, $billerResult->result());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success
     * @return void
     */
    public function existing_credit_card_biller_result_should_have_code(RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->code());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success
     * @return void
     */
    public function existing_credit_card_biller_result_should_have_reason(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success
     * @return void
     */
    public function existing_credit_card_biller_result_should_have_request_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->requestPayload());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller result
     * @depends it_should_return_a_credit_card_biller_result_for_existing_credit_card_charge_when_biller_response_is_success
     * @return void
     */
    public function existing_credit_card_biller_result_should_have_response_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->responsePayload());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_return_a_credit_card_biller_result_for_existing_credit_card_with_declined_status_when_biller_response_is_declined()
    {
        $this->rocketgateClient
            ->method('chargeNewCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => '{"reason_code":"0","response_code":"0","reason_desc":"Success"}',
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );

        $this->assertEquals(RocketgateBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_should_return_a_pending_credit_card_biller_result_when_3ds_auth_is_required()
    {
        $this->rocketgateClient
            ->method('chargeNewCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => "TRUE"],
                        'response' => '{"PAREQ":"SimulatedPAREQ10001705E036897","acsURL":"https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp","reasonCode":"202"}',
                        'reason'   => '202',
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        /** @var RocketgateCreditCardBillerResponse $billerResult */
        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true])
        );

        $this->assertEquals(RocketGateBillerResponse::CHARGE_RESULT_PENDING, $billerResult->result());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_should_return_a_declined_credit_card_biller_result_when_3ds_auth_is_not_required()
    {
        $this->rocketgateClient
            ->method('chargeNewCreditCard')
            ->willReturn(
                json_encode(
                    [
                        'request'  => ["request" => "json", "use3DSecure" => "FALSE"],
                        'response' => '{"PAREQ":"SimulatedPAREQ10001705E036897","acsURL":"https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp","reasonCode":"202"}',
                        'reason'   => '202',
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

        /** @var RocketgateCreditCardBillerResponse $billerResult */
        $billerResult = $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => false])
        );

        $this->assertEquals(RocketGateBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }
}
