<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayRetrieveQrCodeBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\UnitTestCase;

class PumapayTranslatorTest extends UnitTestCase
{
    /**
     * @test
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException
     * @return PumapayBillerResponse
     */
    public function it_should_return_a_valid_translated_response_object_for_retrieve_qr_code(): PumapayBillerResponse
    {
        $translator = new PumapayTranslator();
        $result     = $translator->toRetrieveQrCodeBillerResponse(
            '{ 
               "request":{ 
                  "currency":"EUR",
                  "title":"Brazzers Membership",
                  "description":"1$ day then daily rebill at 1$ for 3 days",
                  "frequency":null,
                  "trialPeriod":null,
                  "numberOfPayments":null,
                  "typeID":2,
                  "amount":100,
                  "initialPaymentAmount":null
               },
               "response":{ 
                  "success":true,
                  "status":200,
                  "message":"Successfully retrieved the QR code.",
                  "data":{
                     "qrImage": "qrCode",
                     "encryptText": "encryptText"
                  }
               },
               "code":200,
               "reason":null
            }',
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(PumapayRetrieveQrCodeBillerResponse::class, $result);

        return $result;
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_reason(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->reason());
    }

    /**
     * @test
     * @param PumapayRetrieveQrCodeBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_qr_code(PumapayRetrieveQrCodeBillerResponse $response): void
    {
        $this->assertNotNull($response->qrCode());
    }

    /**
     * @test
     * @param PumapayRetrieveQrCodeBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_encrypt_text_biller_response_should_contain_a_string(PumapayRetrieveQrCodeBillerResponse $response): void
    {
        $this->assertNotNull($response->encryptText());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_result(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->result());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_code(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->code());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_request_payload(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->requestPayload());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_response_payload(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->responsePayload());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_request_date(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->requestDate());
    }


    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_retrieve_qr_code
     */
    public function retrieve_qr_code_biller_response_should_contain_a_response_date(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->responseDate());
    }

    /**
     * @test
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function it_should_throw_an_invalid_biller_response_exception_when_create_method_fails(): void
    {
        $this->expectException(InvalidBillerResponseException::class);

        $billerResponse = $this->createMock(PumapayRetrieveQrCodeBillerResponse::class);
        $billerResponse->method('create')->willThrowException(new \Exception());

        $translator = new PumapayTranslator();
        $translator->toRetrieveQrCodeBillerResponse(
            '{}',
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    /**
     * @test
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException
     * @return PumapayBillerResponse
     */
    public function it_should_return_a_valid_translated_response_object_for_cancel_rebill(): PumapayBillerResponse
    {
        $translator = new PumapayTranslator();
        $result     = $translator->toCancelRebillBillerResponse(
            '{ 
               "success":true,
               "request":{ 
                  "businessId":"' . $_ENV['PUMAPAY_BUSINESS_ID'] . '",
                  "paymentId":"'. $this->faker->uuid . '"
               },
               "response":{ 
                  "success":true,
                  "status":200
               },
               "code":200,
               "reason":null
            }',
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(PumapayCancelRebillBillerResponse::class, $result);

        return $result;
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_cancel_rebill
     */
    public function it_should_have_a_code_on_cancel_rebill(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->code());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_cancel_rebill
     */
    public function it_should_have_a_request_payload_on_cancel_rebill(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->requestPayload());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_cancel_rebill
     */
    public function it_should_have_a_response_payload_on_cancel_rebill(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->responsePayload());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_cancel_rebill
     */
    public function it_should_have_a_request_date_on_cancel_rebill(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->requestDate());
    }

    /**
     * @test
     * @param PumapayBillerResponse $response Response
     * @return void
     * @depends it_should_return_a_valid_translated_response_object_for_cancel_rebill
     */
    public function it_should_have_a_response_date_on_cancel_rebill(PumapayBillerResponse $response): void
    {
        $this->assertNotNull($response->responseDate());
    }
}
