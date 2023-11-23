<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class PerformRocketgateSaleTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $deprecatedUri = '/api/v1/sale/rocketgate';

    /**
     * @test
     * @return void
     */
    public function successful_create_approved_rocketgate_sale_without_rebill_should_return_201()
    {
        $response = $this->json('POST', $this->deprecatedUri . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70', $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return $response->response->getContent();
    }

    /**
     * @test
     * @return void
     */
    public function successful_create_approved_rocketgate_sale_with_minimum_payload_should_return_201()
    {
        $response = $this->json('POST', $this->deprecatedUri . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70', $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return $response->response->getContent();
    }

    /**
     * @test
     * @depends successful_create_approved_rocketgate_sale_without_rebill_should_return_201
     * @param string $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_id($responseContent)
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
    public function successful_create_rocketgate_sale_response_should_contain_an_status($responseContent)
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

        $response = $this->json(
            'POST', $this->deprecatedUri . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70',
            $this->fullPayload
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     */
    public function it_should_throw_invalid_session_id_exception_when_a_wrong_session_id_is_provided()
    {
        $response = $this->json('POST', $this->deprecatedUri . '/session/1111', $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }
}
