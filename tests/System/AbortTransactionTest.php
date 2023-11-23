<?php

namespace Tests\System;

use Illuminate\Http\Response;
use ProBillerNG\Logger\Log;
use Tests\CreatesTransactionData;
use Tests\SystemTestCase;

class AbortTransactionTest extends SystemTestCase
{
    use CreatesTransactionData;

    /**
     * @test
     * @group legacyService
     * @return array
     */
    public function it_should_return_200_when_successful(): array
    {
        self::markTestSkipped('pumapay tests should be skipped');

        $transaction = $this->createPumapayPendingTransaction();

        $response = $this->put(
            '/api/v1/transaction/' . $transaction['transactionId'] . '/abort'
        );

        $response->assertResponseStatus(Response::HTTP_OK);

        return [
            'response'      => json_decode($response->response->getContent(), true),
            'transactionId' => $transaction['transactionId'],
        ];
    }

    /**
     * @test
     * @depends it_should_return_200_when_successful
     *
     * @param array $response
     *
     * @return void
     */
    public function it_should_return_the_status_inside_the_response_when_successful(array $response): void
    {
        $this->assertArrayHasKey('status', $response['response']);
    }

    /**
     * @test
     * @depends it_should_return_200_when_successful
     *
     * @param array $response
     *
     * @return void
     */
    public function it_should_return_bad_request_when_duplicate_postback_for_same_transaction_id(array $response): void
    {
        $response = $this->put(
            '/api/v1/transaction/' . $response['transactionId'] . '/abort',
        );

        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @depends it_should_return_200_when_successful
     * @group legacyService
     * @return void
     * @throws \Exception
     */
    public function api_with_no_generate_correlation_id_middleware_should_have_correlation_id_on_logger(): void
    {
        $this->assertNotEmpty(Log::getCorrelationId());
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_not_found_when_invalid_transaction_id_provided(): void
    {
        $response = $this->put(
            '/api/v1/transaction/' . $this->faker->uuid . '/abort'
        );

        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return array
     */
    protected function createPumapayPendingTransaction(): array
    {
        $response = $this->post(
            '/api/v1/pumapay/qrCode',
            [
                'siteId'       => 'c465025d-5707-43df-93ed-ebbb0bbedcbc',
                'currency'     => 'EUR',
                'amount'       => 1.99,
                'billerFields' => [
                    'businessId'    => $_ENV['PUMAPAY_BUSINESS_ID'],
                    'businessModel' => $_ENV['PUMAPAY_BUSINESS_MODEL'],
                    'apiKey'        => $_ENV['PUMAPAY_API_KEY'],
                    'title'         => 'Some title',
                    'description'   => 'Membership to pornhubpremium.com for 30 days for a charge of $1.99',
                ],
                'formatAsJson' => true
            ],
            ['x-api-key' => config('security.publicKeys')[0]]
        );

        return json_decode($response->response->getContent(), true);
    }
}
