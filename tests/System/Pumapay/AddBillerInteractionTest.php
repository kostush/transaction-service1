<?php

namespace Pumapay;

use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class AddBillerInteractionTest extends SystemTestCase
{
    const  URI = '/api/v1/pumapay/qrCode';

    /**
     * @test
     * @return array
     */
    public function it_should_return_created_for_successful_sale(): array
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

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @return array
     */
    public function it_should_return_transaction_approved_after_successful_add_biller_interaction($transaction)
    {
        // Payload based on production logs, the sensitive data was changed.
        $payload = [
            "payload"      => [
                "paymentData"     => [
                    "id"                   => $this->faker->password(32),
                    "title"                => "Pumapay TEST Recc only(do not use)",
                    "productID"            => $this->faker->password(32),
                    "businessID"           => $this->faker->password(32),
                    "description"          => "Membership to pornhubpremium.com for 1 day for a charge of $0.30",
                    "amount"               => "40",
                    "initialPaymentAmount" => "30",
                    "trialPeriod"          => "86400",
                    "currency"             => "USD",
                    "hdWalletIndex"        => "607",
                    "numberOfPayments"     => "60",
                    "frequency"            => "86400",
                    "typeID"               => "6",
                    "statusID"             => "2",
                    "networkID"            => "1",
                    "nextPaymentDate"      => "1630091270",
                    "lastPaymentDate"      => "1630004870",
                    "startTimestamp"       => "1630004855",
                    "customerAddress"      => $this->faker->password(42),
                    "pullPaymentAddress"   => $this->faker->password(42),
                    "automatedCashOut"     => "0",
                    "cashOutFrequency"     => "1",
                    "destination"          => "Treasury",
                    "treasuryWallet"       => $this->faker->password(42),
                    "uniqueReferenceID"    => $this->faker->uuid,
                    "topUpTotalSpent"      => "0",
                    "topUpTotalLimit"      => "0",
                    "topUpThreshold"       => "0",
                    "createdAt"            => "2021-08-26T19:07:36.531Z",
                    "updatedAt"            => "2021-08-26T19:07:36.531Z",
                    "dynamicFields"        => "{\"numberOfPayments\":60,\"initialPaymentAmount\":30,\"typeID\":6,\"currency\":\"USD\",\"trialPeriod\":86400,\"amount\":40,\"title\":\"Pumapay TEST Recc only(do not use)\",\"frequency\":86400,\"description\":\"Membership to pornhubpremium.com for 1 day for a charge of $0.30\",\"smartContractID\":12}",
                    "smartContractID"      => "12"
                ],
                "transactionData" => [
                    "id"                => $this->faker->password(32),
                    "hash"              => $this->faker->password(66),
                    "statusID"          => "3",
                    "typeID"            => "5",
                    "paymentID"         => $this->faker->password(32),
                    "timestamp"         => "1630004864",
                    "amount"            => "1278227524499400000000",
                    "createdAt"         => "2021-08-26T19:07:44.143Z",
                    "updatedAt"         => "2021-08-26T19:07:50.172Z",
                    "referenceNo"       => "4075",
                    "index"             => "1",
                    "rate"              => "0.00023470000000000004",
                    "fiatAmount"        => "30",
                    "fiatCurrency"      => "USD",
                    "gasFeeCost"        => "430494",
                    "destination"       => "Treasury",
                    "blockHash"         => $this->faker->password(66),
                    "blockNumber"       => "13102827",
                    "cumulativeGasUsed" => "1059121",
                    "effectiveGasPrice" => "0x1ac688be00",
                    "from"              => $this->faker->password(42),
                    "gasUsed"           => "430494",
                    "logs"              => [

                    ],
                    "logsBloom"         => "",
                    "status"            => "1",
                    "to"                => $this->faker->password(42),
                    "transactionHash"   => $this->faker->password(66),
                    "transactionIndex"  => "6",
                    "type"              => "0x0",
                    "transactionType"   => "register_and_execute"
                ]
            ],
            "formatAsJson" => "1"
        ];

        // Sending Request
        $response = $this->put(
            '/api/v1/transaction/' . $transaction['transactionId'] . '/pumapay/billerInteraction',
            $payload,
            ["x-api-key" => config('security.publicKeys')[0]]
        );

        // Verify success
        $response->assertResponseStatus(\Illuminate\Http\Response::HTTP_OK);

        $dataResponse = json_decode($response->response->getContent());

        // Status should be approved
        $this->assertEquals('approved', $dataResponse->status);
    }
}