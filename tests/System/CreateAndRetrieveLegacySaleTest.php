<?php

namespace tests\System;

use Illuminate\Http\Response;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use Tests\SystemTestCase;

class CreateAndRetrieveLegacySaleTest extends SystemTestCase
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $legacyMemberId;

    /**
     * @var string
     */
    protected $legacySubscriptionId;

    /**
     * @var string
     */
    protected $legacyTransactionId;

    /**
     * @var TransactionRepository
     */
    protected $repository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sessionId            = $this->faker->uuid;
        $this->legacyMemberId       = "145106107";
        $this->legacySubscriptionId = "583545066";
        $this->legacyTransactionId  = "177460261";
        $this->repository           = $this->app->make(TransactionRepository::class);
    }

    /**
     * @test
     * @return void
     * @throws InvalidTransactionInformationException
     */
    public function it_should_store_corresponding_ids_in_database()
    {
        $transactionId = $this->postNewSale();

        $this->addBillerInteraction($transactionId);

        $transaction = $this->repository->findById((string) $transactionId)->toArray();

        self::assertSame($this->legacyMemberId, $transaction['legacyMemberId']);
        self::assertSame($this->legacySubscriptionId, $transaction['legacySubscriptionId']);
        self::assertSame($this->legacyTransactionId, $transaction['legacyTransactionId']);
    }

    /**
     * @return TransactionId
     * @throws InvalidTransactionInformationException
     */
    private function postNewSale(): TransactionId
    {
        $result = $this->json(
            'POST',
            "/api/v1/sale/biller/paygarden/session/{$this->sessionId}",
            [
                "payment"      => [
                    "type"        => "cc",
                    "information" => [
                        "member" => [
                            "address" => "not informed"
                        ]
                    ]
                ],
                "charges"      => [
                    [
                        "siteId"         => "4c22fba2-f883-11e8-8eb2-f2801f1b9fd1",
                        "amount"         => 0,
                        "currency"       => "USD",
                        "productId"      => 123,
                        "isMainPurchase" => true
                    ]
                ],
                "billerFields" => [
                    "returnUrl"   => "http://mg-pg.probiller.com/api/v1/thirdParty/return/",
                    "postbackUrl" => "http://mg-pg.probiller.com/api/v1/thirdParty/postback/",
                    "others"      => [
                        "legacyProductId" => "123",
                    ]
                ]
            ]
        );

        $result->assertResponseStatus(Response::HTTP_CREATED);

        $json = json_decode($result->response->content(), $phpArray = true);

        $this->assertEquals("pending", $json['status']);
        $this->assertArrayHasKey("transactionId", $json);

        return TransactionId::createFromString($json['transactionId']);
    }

    /**
     * @param TransactionId $transactionId Transaction id.
     * @return void
     */
    private function addBillerInteraction(TransactionId $transactionId)
    {
        $this->json(
            'PUT',
            "/api/v1/legacy/billerInteraction/session/{$this->sessionId}",
            [
                "type"            => "postback",
                "statusCode"      => 0,
                "transactionId"   => (string) $transactionId->value(),
                "responsePayload" => [
                    "custom"       => [
                        "mainProductId" => 15,
                        "transactionId" => (string) $transactionId->value()
                    ],
                    "data"         => [
                        "memberDetails"          => [
                            "member_id" => $this->legacyMemberId
                        ],
                        "productId"              => "15",
                        "transactionId"          => $this->legacyTransactionId,
                        "subscriptionId"         => $this->legacySubscriptionId,
                        "allMemberSubscriptions" => [
                            [
                                $this->legacySubscriptionId => []
                            ]
                        ]
                    ],
                    "settleAmount" => 50
                ]
            ]
        );

        $this->assertResponseStatus(Response::HTTP_OK);
    }

    /**
     * @test
     * @return void
     * @throws InvalidTransactionInformationException
     */
    public function it_should_retrieve_stored_legacy_ids()
    {
        $transactionId = $this->postNewSale();

        $this->addBillerInteraction($transactionId);

        $transactionData = $this->retrieveTransaction($transactionId);

        $this->assertEquals($this->legacyTransactionId, $transactionData['transaction']['legacy_transaction_id']);
        $this->assertEquals($this->legacySubscriptionId, $transactionData['transaction']['legacy_subscription_id']);
        $this->assertEquals($this->legacyMemberId, $transactionData['transaction']['legacy_member_id']);
    }

    /**
     * @param TransactionId $transactionId Transaction id.
     * @return mixed
     */
    private function retrieveTransaction(TransactionId $transactionId)
    {
        $result = $this->json(
            'GET',
            '/api/v1/transaction/' . (string) $transactionId->value() . "/session/{$this->sessionId}",
        );

        $this->assertResponseStatus(Response::HTTP_OK);

        return json_decode($result->response->content(), $phpArray = true);
    }
}
