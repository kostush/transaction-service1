<?php

declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Pumapay\Code;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayPostbackAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayRetrieveQrCodeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslatingService;
use Tests\IntegrationTestCase;

class PumapayTranslatingServiceTest extends IntegrationTestCase
{
    /**
     * @test
     * @return PumapayPostbackBillerResponse
     * @throws LoggerException
     */
    public function it_should_return_a_pumapay_postback_biller_response(): PumapayPostbackBillerResponse
    {
        $translatingService = new PumapayTranslatingService(
            $this->createMock(PumapayRetrieveQrCodeAdapter::class),
            app()->make(PumapayPostbackAdapter::class),
            app()->make(PumapayCancelRebillAdapter::class)
        );

        $response = $translatingService->translatePostback(
            json_encode(
                [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 3,
                        'id'       => 'pZVUs7khzqn2kzweUc8ew1dAAKCgZbiJ',
                    ],
                ],
                JSON_THROW_ON_ERROR
            ),
            'join'
        );

        $this->assertInstanceOf(PumapayPostbackBillerResponse::class, $response);

        return $response;
    }

    /**
     * @test
     * @depends it_should_return_a_pumapay_postback_biller_response
     *
     * @param PumapayPostbackBillerResponse $response Pumapay Postback Biller Response
     *
     * @return void
     */
    public function it_should_return_response_with_the_exact_code(PumapayPostbackBillerResponse $response): void
    {
        $this->assertNotEmpty(Code::PUMAPAY_INVALID_TYPE_RECEIVED, $response->code());
    }

    /**
     * @test
     * @depends it_should_return_a_pumapay_postback_biller_response
     *
     * @param PumapayPostbackBillerResponse $response Pumapay Postback Biller Response
     *
     * @return void
     */
    public function it_should_return_response_should_return_400_true(PumapayPostbackBillerResponse $response): void
    {
        $this->assertTrue($response->shouldReturn400());
    }

    /**
     * @test
     * @depends it_should_return_a_pumapay_postback_biller_response
     *
     * @param PumapayPostbackBillerResponse $response Pumapay Postback Biller Response
     *
     * @return void
     */
    public function it_should_return_response_with_exact_reason(PumapayPostbackBillerResponse $response): void
    {
        $message = \sprintf(Code::getMessage((int) $response->code()), 'join', 'rebill');

        $this->assertSame($message, $response->reason());
    }

    /**
     * @test
     * @return PumapayPostbackBillerResponse
     * @throws LoggerException
     */
    public function it_should_return_a_pumapay_postback_biller_response_with_code_200_when_valid_payload_provided(): PumapayPostbackBillerResponse
    {
        $translatingService = new PumapayTranslatingService(
            $this->createMock(PumapayRetrieveQrCodeAdapter::class),
            app()->make(PumapayPostbackAdapter::class),
            app()->make(PumapayCancelRebillAdapter::class)
        );

        $response = $translatingService->translatePostback(
            json_encode(
                [
                    'transactionData' => [
                        'statusID' => 3,
                        'typeID'   => 5,
                        'id'       => 'pZVUs7khzqn2kzweUc8ew1dAAKCgZbiJ',
                    ],
                ],
                JSON_THROW_ON_ERROR
            ),
            'join'
        );

        $this->assertSame('200', $response->code());

        return $response;
    }
}
