<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\UnitTestCase;

class RocketgateCreditCardChargeTranslatorTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_throw_an_invalid_biller_response_exception_when_response_cannot_be_created_from_raw_response(
    )
    {
        $this->expectException(InvalidBillerResponseException::class);
        $translator = new RocketgateCreditCardChargeTranslator();

        $translator->toCreditCardBillerResponse(
        //Invalid response
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        'reason_code'   => '0',
                        'response_code' => '0',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                    ],
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    /**
     * @test
     * @depends it_should_throw_an_invalid_biller_response_exception_when_response_cannot_be_created_from_raw_response
     * @return RocketgateCreditCardBillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        'reason_code'   => '0',
                        'response_code' => '0',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                    ],
                    'reason'   => '111',
                    'code'     => '1',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(RocketgateCreditCardBillerResponse::class, $billerResult);

        return $billerResult;
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_result(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_code(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->code());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_reason(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_request_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->requestPayload());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_response_payload(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->responsePayload());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_the_correct_reason_code(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertEquals(RocketgateErrorCodes::RG_CODE_DECLINED_INVALID_CARD, (int) $billerResult->code());
    }

    /**
     * @test
     * @param RocketgateCreditCardBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_biller_transaction_id(
        RocketgateCreditCardBillerResponse $billerResult
    ) {
        $this->assertNotNull($billerResult->billerTransactionId());
    }

    /**
     * @test
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function created_with_unknown_reason_should_return_default_error()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        'reason_code'   => '0',
                        'response_code' => '0',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                    ],
                    'reason'   => '9999999',
                    'code'     => '1',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertEquals(RocketgateErrorCodes::getMessage((int) $billerResult->code()), 'Unknown error code');
    }

    /**
     * @test
     * @depends it_should_throw_an_invalid_biller_response_exception_when_response_cannot_be_created_from_raw_response
     * @return void
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_return_an_aborted_credit_card_biller_response_if_rocketgate_response_code_3xx()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        'reason_code'   => '0',
                        'response_code' => '0',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                    ],
                    'reason'   => '311',
                    'code'     => '1',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertEquals(RocketgateCreditCardBillerResponse::CHARGE_RESULT_ABORTED, $billerResult->result());
    }

    /**
     * @test
     * @depends it_should_throw_an_invalid_biller_response_exception_when_response_cannot_be_created_from_raw_response
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_declined_credit_card_biller_response_if_rocketgate_response_code_202_no_3ds()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json',
                        'use3DSecure'   => 'FALSE'
                    ],
                    'response' => [
                        'reason_code'   => '202',
                        'response_code' => '2',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                        'PAREQ'         => 'SimulatedPAREQ10001705E036897',
                        'acsURL'        => 'https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp'
                    ],
                    'reason'   => '202',
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertEquals(RocketgateCreditCardBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }

    /**
     * @test
     * @depends it_should_throw_an_invalid_biller_response_exception_when_response_cannot_be_created_from_raw_response
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_pending_credit_card_biller_response_if_rocketgate_response_code_202_with_3ds()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => 'TRUE'
                    ],
                    'response' => [
                        'reason_code'   => '202',
                        'response_code' => '2',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                        'PAREQ'         => 'SimulatedPAREQ10001705E036897',
                        'acsURL'        => 'https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp'
                    ],
                    'reason'   => '202',
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertEquals(RocketgateCreditCardBillerResponse::CHARGE_RESULT_PENDING, $billerResult->result());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_should_return_a_declined_credit_card_biller_result_when_3ds_auth_is_not_required()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => false
                    ],
                    'response' => [
                        'reason_code'   => '202',
                        'response_code' => '2',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                        'PAREQ'         => 'SimulatedPAREQ10001705E036897',
                        'acsURL'        => 'https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp'
                    ],
                    'reason'   => '202',
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertEquals(RocketgateCreditCardBillerResponse::CHARGE_RESULT_DECLINED, $billerResult->result());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_should_return_a_pending_credit_card_biller_result_when_3ds_auth_is_required()
    {
        $translator = new RocketgateCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => true
                    ],
                    'response' => [
                        'reason_code'   => '202',
                        'response_code' => '2',
                        'reason_desc'   => 'Success',
                        'guidNo'        => '100016A02ZZZZZZ',
                        'PAREQ'         => 'SimulatedPAREQ10001705E036897',
                        'acsURL'        => 'https:\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp'
                    ],
                    'reason'   => '202',
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );


        $this->assertEquals(RocketgateCreditCardBillerResponse::CHARGE_RESULT_PENDING, $billerResult->result());
    }
}
