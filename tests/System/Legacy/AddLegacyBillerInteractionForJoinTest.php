<?php
declare(strict_types=1);

namespace Tests\System\Legacy;

use Illuminate\Http\Response;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;
use Tests\SystemTestCase;

/**
 * @group legacyService
 * Class AddLegacyBillerInteractionForJoinTest
 * @package Tests\System\Legacy
 */
class AddLegacyBillerInteractionForJoinTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $newSaleUri = '/api/v1/sale/biller/vendo/session/b0c56eaf-0ce8-4884-9bae-076c69a2b0af';

    /**
     * @var string
     */
    private $addBillerInteractionUri = '/api/v1/legacy/billerInteraction/session/eb23f018-eecf-41f8-92fc-61f977458d98';


    /**
     * @param string $transactionId         Transaction Id.
     * @param array  $arrayWithCustomFields Array with custom Fields
     * @return array
     */
    private function returnPayload(string $transactionId = null, array $arrayWithCustomFields = []): array
    {
        return [
            'transactionId'   => $transactionId ?? $this->faker->uuid,
            'statusCode'      => $arrayWithCustomFields['statusCode'] ?? 0,
            'type'            => 'return',
            'responsePayload' => [
                'custom'          => [
                    'transactionId' => '6f610828-edc4-45e2-b4fd-00a19eff2d01',
                    'mainProductId' => '44573'
                ],
                'code'            => '0',
                'time'            => '1597855297.6186',
                'connectionId'    => '1599164221',
                'message'         => 'Purchase was successful.',
                'data'            => [
                    'memberDetails'          => [
                        'member_id'    => '401240661',
                        'member_uuid'  => 'ced9e288-5d07-4654-8b08-a1a50dac5dee',
                        'email'        => 'nsdgpostbacktest@test.mindgeek.com',
                        'phone_number' => '',
                        'first_name'   => 'mindgeek',
                        'last_name'    => 'mindgeek',
                        'address'      => 'testing',
                        'city'         => 'testing ville',
                        'state'        => 'QC',
                        'zip'          => 'h1h1h1',
                        'country'      => 'DE',
                        'username'     => '1DoSPaQpEyR',
                        'password'     => 'bbY6gvQTzb',
                    ],
                    'transactionDetails'     => [
                        'transaction_id'  => '1990453391',
                        'type'            => 'SALE',
                        'amount'          => '16.70',
                        'currency'        => 'USD',
                        'last4'           => null,
                        'first6'          => null,
                        'three_d_secured' => '0',
                        'prepaid_card'    => '0',
                    ],
                    'transactionId'          => '1990453391',
                    'productId'              => '44573',
                    'postbackUrl'            => 'https://ams-postback-capture-service.probiller.com/api/postbacks/41',
                    'statementDescriptor'    => 'WWW.BRAZZERS.COM',
                    'subscriptionId'         => '403682551',
                    'allMemberSubscriptions' => [
                        403682551 => [
                            'payment_template'      => '238213264',
                            'product_identifier'    => '44573',
                            'product_password'      => '',
                            'rebill_amount'         => '19.95',
                            'rebill_days'           => '30',
                            "initial_days"          => '30',
                            'recurring'             => [
                                0 => [
                                    'id'                   => '328270691',
                                    'nextRebillDate'       => '2020-09-18 16:19:12',
                                    'rebillDays'           => '30',
                                    'rebillAmount'         => '19.95',
                                    'lastFailedRebillDate' => null,
                                    'gracePeriodEndDate'   => '2020-10-03 16:19:12',
                                    'created'              => '2020-08-19 16:19:12',
                                ],
                            ],
                            'recurringId'           => '328270691',
                            'require_active_parent' => '0',
                            'site_id'               => '2',
                            'status'                => 'ACTIVE',
                            'subscriptionUUID'      => '937fc5ea-1c3f-4971-8a80-be1d95225b2b',
                            'subscription_id'       => '403682551',
                            'subsite_id'            => null,
                            'sync_from_auth_system' => '2',
                            'trial'                 => '0',
                            'username'              => '1DoSPaQpEyR',
                        ],
                    ],
                ],
                'transactionType' => 'SALE',
                'settleAmount'    => '16.70',
                'settleCurrency'  => 'USD'
            ]
        ];
    }

    /**
     * @test
     * @return string
     */
    public function it_should_update_transaction_to_approved_when_the_purchase_was_approved(): string
    {
        $transactionId = $this->returnTransactionIdFromNewSaleOperation();

        $payload = $this->returnPayload($transactionId);

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );

        $responseContent = json_decode($response->response->getContent(), true);
        $response->assertResponseStatus(Response::HTTP_OK);
        $this->assertSame(LegacyPostbackBillerResponse::STATUS_APPROVED, $responseContent['status']);
        return $transactionId;
    }

    /**
     * @test
     * @return void
     */
    public function it_should_update_transaction_to_declined_when_the_purchase_was_declined(): void
    {
        $transactionId = $this->returnTransactionIdFromNewSaleOperation();

        $payload               = $this->returnPayload($transactionId);
        $payload['statusCode'] = 1;

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );

        $responseContent = json_decode($response->response->getContent(), true);
        $response->assertResponseStatus(Response::HTTP_OK);
        $this->assertSame(LegacyPostbackBillerResponse::STATUS_DECLINED, $responseContent['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_not_found_if_the_pedding_transaction_was_not_found(): void
    {
        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $this->returnPayload()
        );

        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @depends it_should_update_transaction_to_approved_when_the_purchase_was_approved
     * @param string $transactionId Transaction Id
     * @return void
     */
    public function it_returns_success_when_it_is_to_update_an_non_pending_transaction(string $transactionId): void
    {
        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $this->returnPayload($transactionId)
        );

        $response->assertResponseStatus(Response::HTTP_OK);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_create_cross_sale_when_custom_product_id_is_different_from_data_product_id(): void
    {
        $transactionId = $this->returnTransactionIdFromNewSaleOperation();

        $payload                                         = $this->returnPayload($transactionId);
        $payload['siteId']                               = '11eb4634-45b5-411b-a276-3ac89c7b6d83';
        $payload['responsePayload']['data']['productId'] = '191';

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );

        $responseContent = json_decode($response->response->getContent(), true);
        $this->assertNotEmpty($responseContent['transactionId']);
        $this->assertNotEquals($transactionId, $responseContent['transactionId']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_not_create_cross_sale_when_it_declined_even_custom_product_id_is_different_from_data_product_id(): void
    {
        $declinedStatusCode = 1;
        $transactionId      = $this->returnTransactionIdFromNewSaleOperation();

        $payload                                         = $this->returnPayload($transactionId);
        $payload['siteId']                               = '11eb4634-45b5-411b-a276-3ac89c7b6d83';
        $payload['responsePayload']['data']['productId'] = '191';
        $payload['statusCode']                           = $declinedStatusCode;

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );

        $responseContent = json_decode($response->response->getContent(), true);
        $this->assertEquals($transactionId, $responseContent['transactionId']);
        $this->assertSame(LegacyPostbackBillerResponse::STATUS_DECLINED, $responseContent['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_bad_request_if_site_id_not_provided_when_it_is_cross_sale(): void
    {
        $transactionId = $this->returnTransactionIdFromNewSaleOperation();

        $payload                                         = $this->returnPayload($transactionId);
        $payload['responsePayload']['data']['productId'] = '191';

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );

        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @param array $arrayWithInvalidTypes array with invalid types
     * @dataProvider requestInvalidField
     * @return       void
     */
    public function it_should_returning_bad_request_for_invalid_field_types(array $arrayWithInvalidTypes): void
    {
        $transactionId = $this->returnTransactionIdFromNewSaleOperation();

        $payload = $this->returnPayload($transactionId, $arrayWithInvalidTypes);

        $response = $this->json(
            'PUT',
            $this->addBillerInteractionUri,
            $payload
        );
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function requestInvalidField(): array
    {
        $decimalStatusCode['statusCode']      = 0.1;
        $lessThanZeroStatusCode['statusCode'] = -1;

        return [
            'less_than_zero_status_code' => [$lessThanZeroStatusCode],
            'decimal_status_code'        => [$decimalStatusCode],
        ];
    }

    /**
     * @return string
     */
    private function returnTransactionIdFromNewSaleOperation(): string
    {
        $response = $this->json(
            'POST',
            $this->newSaleUri,
            $this->newSalePayload()
        );

        $responseContent = json_decode($response->response->getContent(), true);
        return $responseContent['transactionId'];
    }

    /**
     * @return array
     */
    private function newSalePayload(): array
    {
        return [
            'payment'      => [
                'type'        => 'cc',
                'method'      => 'visa',
                'information' => [
                    'member' => [
                        "firstName" => "Centrbill",
                        "lastName"  => "sdasdsdsd",
                        "userName"  => "asdasdadad",
                        "password"  => "123456test",
                        "email"     => "aa.ff@test.mindgeek.com",
                        "phone"     => "514 000-0000",
                        "address"   => "7777 Decarie",
                        "zipCode"   => "H1H1H1",
                        "city"      => "Montreal",
                        "state"     => "QC",
                        "country"   => "CA"
                    ]
                ]
            ],
            'charges' => [
                [
                    'siteId'         => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
                    "amount"         => "14.97",
                    "currency"       => "USD",
                    "productId"      => 15,
                    "isMainPurchase" => true,
                    'rebill'         => [
                        'amount'    => "10",
                        'frequency' => 365,
                        'start'     => 30
                    ],
                    'tax'            => [
                        'initialAmount'    => [
                            'beforeTaxes' => 14.23,
                            'taxes'       => 0.74,
                            'afterTaxes'  => 14.97
                        ],
                        'rebillAmount'     => [
                            'beforeTaxes' => 9.5,
                            'taxes'       => 0.5,
                            'afterTaxes'  => 10
                        ],
                        'taxApplicationId' => '60bf5bcb-ac64-496c-acc5-9c7cf54a1869',
                        'taxName'          => 'Tax Name',
                        'taxRate'          => 0.05,
                        'taxType'          => 'vat'
                    ]
                ]
            ],
            'billerFields' => [
                'legacyMemberId' => 101988,
                'returnUrl'      => 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt',
                'postbackUrl'    => 'http://postback-purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt'
            ]
        ];
    }
}
