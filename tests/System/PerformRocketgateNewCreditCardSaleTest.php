<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class PerformRocketgateNewCreditCardSaleTest extends SystemTestCase
{
    /**
     * @test
     * @return string
     */
    public function successful_create_approved_rocketgate_sale_without_rebill_should_return_201(): string
    {
        $response = $this->json('POST', $this->newSaleUrl, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return $response->response->getContent();
    }

    /**
     * @test
     * @return string
     */
    public function successful_create_approved_rocketgate_sale_with_minimum_payload_should_return_201(): string
    {
        $response = $this->json('POST', $this->newSaleUrl, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return $response->response->getContent();
    }

    /**
     * @test
     * @depends successful_create_approved_rocketgate_sale_without_rebill_should_return_201
     * @param string $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_id(string $responseContent)
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('transactionId', $responseContent);
    }

    /**
     * @test
     * @depends successful_create_approved_rocketgate_sale_without_rebill_should_return_201
     * @param string $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_status(string $responseContent)
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('status', $responseContent);
    }

    /**
     * @test
     * @return void
     */
    public function successful_create_rocketgate_sale_with_rebill_should_return_201()
    {
        $payload           = $this->fullPayload;
        $payload['rebill'] = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => $this->faker->numberBetween(1, 100),
            "start"     => $this->faker->numberBetween(1, 100)
        ];

        $response = $this->json('POST', $this->newSaleUrl, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_201_when_threeD_is_used(): array
    {
        $payload                                  = $this->fullPayload;
        $payload['billerFields']['sharedSecret']  = $_ENV['ROCKETGATE_SHARED_SECRET_5'];
        $payload['billerFields']['simplified3DS'] = true;
        $payload['useThreeD']                     = true;
        $payload['returnUrl']                     = 'http://www.return-url';

        $response = $this->json('POST', $this->newSaleUrl, $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_threeD_is_used
     * @param array $response Response
     * @return void
     */
    public function it_should_return_transaction_id_when_threeD_is_used(array $response): void
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @depends it_should_return_201_when_threeD_is_used
     * @param array $response Response
     * @return void
     */
    public function it_should_return_status_pending_when_threeD_is_used(array $response)
    {
        $this->assertSame('pending', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_201_when_threeD_is_used
     * @param array $response Response
     * @return array
     */
    public function it_should_return_threeD_info_when_threeD_is_used(array $response): array
    {
        $this->assertArrayHasKey('threeD', $response);

        return $response['threeD'];
    }

    /**
     * @test
     * @depends it_should_return_threeD_info_when_threeD_is_used
     * @param array $threeDInfo ThreeD info.
     * @return void
     */
    public function it_should_return_threeD_info_with_payment_link_url_when_threeD_is_used(array $threeDInfo): void
    {
        $this->assertArrayHasKey('paymentLinkUrl', $threeDInfo);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_201_when_a_non_eligible_card_for_threeD_is_used(): array
    {
        $payload              = $this->fullPayload;
        $payload['useThreeD'] = true;
        $payload['returnUrl'] = 'http://www.return-url';
        unset($payload['billerFields']['merchantAccount']);
        $payload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_NOT_ELLIGIBLE']; // non eligible card


        $response = $this->json('POST', $this->newSaleUrl, $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_a_non_eligible_card_for_threeD_is_used
     * @param array $response Response
     * @return void
     */
    public function it_should_return_status_pending_when_a_non_eligible_card_for_threeD_is_used(array $response): void
    {
        self::assertSame('approved', $response['status']);
    }

    /**
     * @test
     * @return array
     */
    public function decline_transaction_should_contain_mapping_criteria_and_error_classification(): array
    {
        // force a declined transaction
        $this->fullPayload['amount'] = 0.01;

        $response = $this->json('POST', $this->newSaleUrl, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_status(array $responseContent): void
    {
        $this->assertArrayHasKey('status', $responseContent);
        $this->assertEquals('declined', $responseContent['status']);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_error_classification(array $responseContent): void
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_mapping_criteria(array $responseContent): void
    {
        $this->assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }

    /**
     * @test
     * @return array
     */
    public function decline_NSF_transaction_should_contain_mapping_criteria_and_error_classification(): array
    {
        // force a declined NSF transaction
        $this->fullPayload['amount'] = 0.02;

        $response = $this->json('POST', $this->newSaleUrl, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends decline_NSF_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_NSF_transaction_response_should_contain_decline_status(array $responseContent): void
    {
        $this->assertEquals(Declined::NAME, $responseContent['status']);
    }

    /**
     * @test
     * @depends decline_NSF_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_NSF_transaction_response_should_contain_declined_overlimit_code(array $responseContent): void
    {
        $this->assertEquals(RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT, $responseContent['code']);
    }

    /**
     * @test
     * @depends decline_NSF_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_NSF_transaction_response_should_contain_error_classification(array $responseContent): void
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
    }

    /**
     * @test
     * @depends decline_NSF_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_NSF_transaction_response_should_contain_mapping_criteria(array $responseContent): void
    {
        $this->assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }
}
