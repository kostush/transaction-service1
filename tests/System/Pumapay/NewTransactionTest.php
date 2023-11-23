<?php

namespace Pumapay;

use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class NewTransactionTest extends SystemTestCase
{
    const  URI = '/api/v1/pumapay/qrCode';

    /**
     * @test
     * @return array
     */
    public function it_should_return_created_for_successful_sale_without_rebill(): array
    {
        $this->markTestSkipped('pumapay tests should be skipped');

        $payload = [
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
        ];

        $response = $this->json('POST', self::URI, $payload, ["x-api-key" => config('security.publicKeys')[0]]);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $responseDecoded = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('transactionId', $responseDecoded);
        $this->assertArrayHasKey('qrCode', $responseDecoded);
        $this->assertArrayHasKey('encryptText', $responseDecoded);

        return $responseDecoded;
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_without_rebill
     */
    public function it_should_return_transaction_after_successful_creation($transaction)
    {
        $response = $this->get(
            '/api/v1/transaction/' . $transaction['transactionId'] . '/session/' . $this->faker->uuid,
            ['sessionId' => $this->faker->uuid]
        );

        $response->assertResponseStatus(\Illuminate\Http\Response::HTTP_OK);
        $dataResponse = json_decode($response->response->getContent());
        $this->assertEquals('pending', $dataResponse->transaction->status);
        $this->assertEquals('crypto', $dataResponse->payment_type);
        $this->assertEquals('pumapay', $dataResponse->biller_name);
    }
}