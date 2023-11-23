<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardChargeTranslator;
use Tests\UnitTestCase;

class NetbillingCreditCardChargeTranslatorTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_throw_biller_response_exception_when_response_cannot_be_created_from_raw_response(): void
    {
        $this->expectException(InvalidBillerResponseException::class);
        $translator = new NetbillingCreditCardChargeTranslator();

        $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        "avs_code"        => "X",
                        "cvv2_code"       => "M",
                        "status_code"     => "1",
                        "processor"       => "TEST",
                        "auth_code"       => "999999",
                        "settle_amount"   => "1.00",
                        "settle_currency" => "USD",
                        "trans_id"        => "114152087528",
                        "member_id"       => "114152087529",
                        "auth_msg"        => "TEST APPROVED",
                        "recurring_id"    => "114152103912",
                        "auth_date"       => "2019-11-26 20:07:58"
                    ]
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    /**
     * @test
     * @depends it_should_throw_biller_response_exception_when_response_cannot_be_created_from_raw_response
     * @return NetbillingBillerResponse
     * @throws \Exception
     */
    public function it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent(): NetbillingBillerResponse
    {
        $translator = new NetbillingCreditCardChargeTranslator();

        $billerResult = $translator->toCreditCardBillerResponse(
            json_encode(
                [
                    'request'  => [
                        'request' => 'json'
                    ],
                    'response' => [
                        "avs_code"        => "X",
                        "cvv2_code"       => "M",
                        "status_code"     => "1",
                        "processor"       => "TEST",
                        "auth_code"       => "999999",
                        "settle_amount"   => "1.00",
                        "settle_currency" => "USD",
                        "trans_id"        => "114152087528",
                        "member_id"       => "114152087529",
                        "auth_msg"        => "TEST APPROVED",
                        "recurring_id"    => "114152103912",
                        "auth_date"       => "2019-11-26 20:07:58"
                    ],
                    'reason'   => '111',
                    'code'     => '0',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(NetbillingBillerResponse::class, $billerResult);

        return $billerResult;
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_result(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_code(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->code());
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_reason(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->reason());
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_request_payload(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->requestPayload());
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_response_payload(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->responsePayload());
    }

    /**
     * @test
     * @param NetbillingBillerResponse $billerResult Biller Result
     * @depends it_should_return_an_approved_credit_card_biller_response_if_correct_json_sent
     * @return void
     */
    public function approved_credit_card_biller_response_should_contain_a_biller_transaction_id(
        NetbillingBillerResponse $billerResult
    ): void {
        $this->assertNotNull($billerResult->billerTransactionId());
    }
}
