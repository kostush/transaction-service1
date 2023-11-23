<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class PerformExistingCreditCardRocketgateSaleTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $uri = '/api/v1/sale/existingCard/rocketgate/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';


    /**
     * @test
     * @return string
     */
    public function successful_create_approved_rocketgate_sale_without_rebill_should_return_201(): string
    {
        $response = $this->json('POST', $this->uri, $this->existingCardFullPayload);

        $response->assertResponseStatus(Response::HTTP_CREATED);

        return $response->response->getContent();
    }

    /**
     * @test
     * @return array
     */
    public function existing_cc_with_3ds_transaction_initialization_should_succeed(): array
    {
        $response = $this->json('POST', $this->uri, $this->existingCardWithTreeDSFullPayload);

        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends existing_cc_with_3ds_transaction_initialization_should_succeed
     * @param array $response Response.
     * @return void
     */
    public function successful_transaction_initialization_with_existing_cc_response_should_contain_a_transaction_id(array $response): void
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @depends existing_cc_with_3ds_transaction_initialization_should_succeed
     * @param array $response Response.
     * @return void
     */
    public function successful_transaction_initialization_with_existing_cc_response_should_contain_pending_status(array $response): void
    {
        $this->assertEquals('pending', $response['status']);
    }

    /**
     * @test
     * @depends existing_cc_with_3ds_transaction_initialization_should_succeed
     * @param array $response Response.
     * @return array
     */
    public function successful_transaction_initialization_with_existing_cc_response_should_contain_three_d_info(array $response): array
    {
        $this->assertArrayHasKey('threeD', $response);

        return $response['threeD'];
    }

    /**
     * @test
     * @depends successful_transaction_initialization_with_existing_cc_response_should_contain_three_d_info
     * @param array $threeDInfo ThreeD information.
     * @return void
     */
    public function successful_transaction_initialization_with_existing_cc_response_should_contain_three_d_with_payment_link_url(array $threeDInfo): void
    {
        $this->assertArrayHasKey('paymentLinkUrl', $threeDInfo);
    }

    /**
     * @test
     * @return string
     */
    public function successful_create_approved_rocketgate_sale_with_minimum_payload_should_return_201()
    {
        $response = $this->json('POST', $this->uri, $this->existingCardMinPayload);

        $response->assertResponseStatus(Response::HTTP_CREATED);

        return $response->response->getContent();
    }

    /**
     * @test
     * @depends successful_create_approved_rocketgate_sale_without_rebill_should_return_201
     * @param string $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_id(string $responseContent): void
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

        $response = $this->json('POST', $this->uri, $this->existingCardFullPayload);

        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @return array
     */
    public function force_decline_with_wrong_hash_card_should_return_403_from_biller_and_contain_default_error_classification(): array
    {
        // force declined transaction
        $this->existingCardMinPayload['amount'] = 0.01;

        // invalid card hash
        $this->existingCardMinPayload['payment']['information']['cardHash'] = str_replace('m', 'x', $_ENV['ROCKETGATE_CARD_HASH_1']);

        $response = $this->json('POST', $this->uri, $this->existingCardMinPayload);

        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends force_decline_with_wrong_hash_card_should_return_403_from_biller_and_contain_default_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_with_403_response_should_contain_decline_status(array $responseContent) : void
    {
        $this->assertArrayHasKey('status', $responseContent);
        $this->assertEquals('declined', $responseContent['status']);
    }

    /**
     * @test
     * @depends force_decline_with_wrong_hash_card_should_return_403_from_biller_and_contain_default_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_with_403_response_should_contain_403_code(array $responseContent) : void
    {
        $this->assertArrayHasKey('code', $responseContent);
        $this->assertEquals(403, $responseContent['code']);
    }

    /**
     * @test
     * @depends force_decline_with_wrong_hash_card_should_return_403_from_biller_and_contain_default_error_classification
     *
     * @param array $responseContent Response
     *
     * @return array
     */
    public function decline_with_403_response_should_contain_error_classification(array $responseContent) : array
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
        return $responseContent['errorClassification'];
    }

    /**
     * @test
     * @depends decline_with_403_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return void
     */
    public function error_classification_should_contain_groupDecline(array $errorClassification) : void
    {
        $this->assertArrayHasKey('groupDecline', $errorClassification);
    }

    /**
     * @test
     * @depends decline_with_403_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return void
     */
    public function error_classification_should_contain_errorType(array $errorClassification) : void
    {
        $this->assertArrayHasKey('errorType', $errorClassification);
    }

    /**
     * @test
     * @depends decline_with_403_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return void
     */
    public function error_classification_should_contain_groupMessage(array $errorClassification) : void
    {
        $this->assertArrayHasKey('groupMessage', $errorClassification);
    }

    /**
     * @test
     * @depends decline_with_403_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return void
     */
    public function error_classification_should_contain_recommendedAction(array $errorClassification) : void
    {
        $this->assertArrayHasKey('recommendedAction', $errorClassification);
    }

    /**
     * @test
     * @depends decline_with_403_response_should_contain_error_classification
     *
     * @param array $errorClassification Response
     *
     * @return void
     */
    public function error_classification_should_contain_mappingCriteria(array $errorClassification) : void
    {
        $this->assertArrayHasKey('mappingCriteria', $errorClassification);
    }
}
