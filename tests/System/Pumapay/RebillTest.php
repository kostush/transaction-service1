<?php

namespace Pumapay;

use ProBillerNG\Pumapay\Domain\Services\PostbackTranslateService;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

/**
 *
 * This test simulates the complete rebill flow
 * 1st makes a transaction
 * 2nd makes a successful postback update
 * 2th makes a successful rebill
 *
 * Class RebillTest
 * @package Pumapay
 */
class RebillTest extends SystemTestCase
{
    const  URI = '/api/v1/pumapay/qrCode';

    /**
     * @test
     * @return string
     */
    public function it_should_return_created_for_successful_sale_with_rebill(): string
    {
        $this->markTestSkipped('pumapay tests should be skipped');

        $payload = [
            'siteId'       => '299b14d0-cf3d-11e9-8c91-0cc47a283dd2',
            'billerId'     => '12345',
            'currency'     => 'EUR',
            'amount'       => 0.3,
            "rebill"       => [
                "frequency" => 1,
                "amount"    => 0.4,
                "start"     => 1
            ],
            'billerFields' => [
                'businessId'    => $_ENV['PUMAPAY_BUSINESS_ID'],
                'businessModel' => $_ENV['PUMAPAY_BUSINESS_MODEL'],
                'apiKey'        => $_ENV['PUMAPAY_API_KEY'],
                'title'         => 'Pumapay TEST Recc only(do not use)',
                'description'   => 'Membership to pornhubpremium.com for 1 day for a charge of $0.30',
            ],
            'formatAsJson' => true
        ];

        $response = $this->json('POST', self::URI, $payload, ["x-api-key" => config('security.publicKeys')[0]]);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true)['transactionId'];
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_with_rebill
     *
     * @param string $transactionId
     *
     * @return string
     */
    public function it_should_return_transaction_approved_after_successful_add_biller_interaction(string $transactionId)
    {
        $payload = $this->buildSuccessPostbackPayload();

        // Sending Request
        $response = $this->put(
            '/api/v1/transaction/' . $transactionId . '/pumapay/billerInteraction',
            $payload,
            ["x-api-key" => config('security.publicKeys')[0]]
        );

        // Verify success
        $response->assertResponseStatus(\Illuminate\Http\Response::HTTP_OK);

        // Status should be approved
        $dataResponse = json_decode($response->response->getContent());
        $this->assertEquals('approved', $dataResponse->status);

        return $transactionId;
    }

    /**
     * @test
     * @depends it_should_return_transaction_approved_after_successful_add_biller_interaction
     *
     * @param string $previousTransactionId
     */
    public function it_should_return_transaction_after_successful_rebill(string $previousTransactionId): void
    {
        $uri     = 'api/v1/pumapay/rebill/billerInteraction';
        $payload = $this->buildRebillPayload($previousTransactionId);

        $response        = $this->json('POST', $uri, $payload, ["x-api-key" => config('security.publicKeys')[0]]);
        $responseDecoded = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('transactionId', $responseDecoded);
        $this->assertEquals('approved', $responseDecoded['status']);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @depends it_should_return_transaction_approved_after_successful_add_biller_interaction
     *
     * @param string $previousTransactionId
     */
    public function it_should_return_transaction_after_failed_rebill(string $previousTransactionId): void
    {
        $uri     = 'api/v1/pumapay/rebill/billerInteraction';
        $payload = $this->buildRebillPayload(
            $previousTransactionId,
            PostbackTranslateService::TRANSACTION_DATA_STATUS_FAILED
        );

        $response        = $this->json('POST', $uri, $payload, ["x-api-key" => config('security.publicKeys')[0]]);
        $responseDecoded = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('transactionId', $responseDecoded);
        $this->assertEquals('declined', $responseDecoded['status']);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @param string $previousTransactionId
     *
     * @param int    $statusId
     *
     * @return array
     */
    private function buildRebillPayload(
        string $previousTransactionId,
        int $statusId = PostbackTranslateService::TRANSACTION_DATA_STATUS_SUCCESS
    ): array {
        // Payload based on production logs, the sensitive data was changed.
        return [
            "previousTransactionId" => $previousTransactionId,
            "payload"               => [
                "paymentData"     => [
                    "id"                   => $this->faker->password(42),
                    "title"                => "Pumapay TEST Recc only(do not use)",
                    "productID"            => $this->faker->password(42),
                    "businessID"           => $this->faker->password(42),
                    "description"          => "Membership to pornhubpremium.com for 1 day for a charge of $0.30",
                    "amount"               => 40,
                    "initialPaymentAmount" => 30,
                    "trialPeriod"          => 86400,
                    "currency"             => "USD",
                    "hdWalletIndex"        => 284,
                    "numberOfPayments"     => 59,
                    "frequency"            => 86400,
                    "typeID"               => 6,
                    "statusID"             => 2,
                    "networkID"            => 1,
                    "nextPaymentDate"      => 1631817714,
                    "lastPaymentDate"      => 1631731314,
                    "startTimestamp"       => "1631644838",
                    "customerAddress"      => $this->faker->password(42),
                    "pullPaymentAddress"   => $this->faker->password(42),
                    "automatedCashOut"     => false,
                    "cashOutFrequency"     => 1,
                    "destination"          => "Treasury",
                    "treasuryWallet"       => $this->faker->password(42),
                    "uniqueReferenceID"    => $previousTransactionId,
                    "topUpTotalSpent"      => 0,
                    "topUpTotalLimit"      => 0,
                    "topUpThreshold"       => 0,
                    "createdAt"            => "2021-09-14T18:40:39.020Z",
                    "updatedAt"            => "2021-09-14T18:40:39.020Z",
                    "deletedAt"            => null,
                    "dynamicFields"        => "{\"numberOfPayments\":60,\"description\":\"Membership to pornhubpremium.com for 1 day for a charge of $0.30\",\"initialPaymentAmount\":30,\"frequency\":86400,\"trialPeriod\":86400,\"amount\":40,\"typeID\":6,\"currency\":\"USD\",\"title\":\"Pumapay TEST Recc only(do not use)\",\"smartContractID\":12}",
                    "smartContractID"      => 12
                ],
                "transactionData" => [
                    "id"                => $this->faker->password(42),
                    "hash"              => "0x339ca45b363c7bc07737b114ba25b0639492224fa91f06778713d15364b35f16",
                    "statusID"          => $statusId,
                    "typeID"            => 3,
                    "paymentID"         => $this->faker->password(42),
                    "timestamp"         => "1631731281",
                    "amount"            => "2087682672233800000000",
                    "createdAt"         => "2021-09-15T18:41:21.777Z",
                    "updatedAt"         => "2021-09-15T18:41:54.831Z",
                    "deletedAt"         => null,
                    "referenceNo"       => 4079,
                    "index"             => "2",
                    "rate"              => "0.0001916",
                    "fiatAmount"        => "40",
                    "fiatCurrency"      => "USD",
                    "gasFeeCost"        => "0.022480314",
                    "destination"       => "Treasury",
                    "pspSettlementID"   => null,
                    "blockHash"         => $this->faker->password(42),
                    "blockNumber"       => 13231995,
                    "contractAddress"   => null,
                    "cumulativeGasUsed" => 1011511,
                    "effectiveGasPrice" => "0x3c11ff9400",
                    "from"              => $this->faker->password(42),
                    "gasUsed"           => 87133,
                    "logs"              => [
                        [
                            "address"          => $this->faker->password(42),
                            "blockHash"        => $this->faker->password(42),
                            "blockNumber"      => 13231995,
                            "data"             => $this->faker->password(42),
                            "logIndex"         => 30,
                            "removed"          => false,
                            "topics"           => [
                                $this->faker->password(42),
                                $this->faker->password(42),
                                $this->faker->password(42),
                            ],
                            "transactionHash"  => $this->faker->password(42),
                            "transactionIndex" => 7,
                            "id"               => "log_5ad10cea"
                        ],
                        [
                            "address"          => $this->faker->password(42),
                            "blockHash"        => $this->faker->password(42),
                            "blockNumber"      => 13231995,
                            "data"             => $this->faker->password(42),
                            "logIndex"         => 31,
                            "removed"          => false,
                            "topics"           => [$this->faker->password(42)],
                            "transactionHash"  => $this->faker->password(42),
                            "transactionIndex" => 7,
                            "id"               => "log_5b7314e3"
                        ]
                    ],
                    "logsBloom"         => $this->faker->password(42),
                    "status"            => true,
                    "to"                => $this->faker->password(42),
                    "transactionHash"   => $this->faker->password(42),
                    "transactionIndex"  => 7,
                    "type"              => "0x0"
                ]
            ],
            "formatAsJson"          => true
        ];
    }

    /**
     * @return array
     */
    private function buildSuccessPostbackPayload(): array
    {
        // Payload based on production logs, the sensitive data was changed.
        return [
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
    }
}