<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class PerformRocketgateUpdateRebillTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $newCardSaleUri = '/api/v1/sale/newCard/rocketgate/session/1ad9a902-3b16-4d14-a7e9-21cf93404e8e';

    /**
     * @var string
     */
    private $updateRebillUri = '/api/v1/updateRebill/rocketgate/session/97d1460b-eb3a-475b-a2b3-d8c51c449aa9';

    /**
     * @var array
     */
    private $newCardSalePayload;

    /**
     * @var array
     */
    private $updateRebillPayload;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $merchantSiteId = $this->faker->numberBetween(1, 100);

        $updateBillerFields = [
            "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
            "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
            "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
            "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true)
        ];

        $billerFields = $updateBillerFields + [
                "merchantSiteId"    => (string) $merchantSiteId,
                "merchantProductId" => $this->faker->uuid,
                "ipAddress"         => $this->faker->ipv4,
                "sharedSecret"      => $this->faker->word,
                "simplified3DS"     => false,
            ];

        $this->newCardSalePayload = [
            "siteId"       => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "number"          => $this->faker->creditCardNumber('Visa'),
                    "expirationMonth" => $this->faker->numberBetween(1, 12),
                    "expirationYear"  => 2030,
                    "cvv"             => (string) $this->faker->numberBetween(100, 999),
                    "member"          => [
                        "firstName" => $this->faker->name,
                        "lastName"  => $this->faker->lastName,
                        "email"     => $this->faker->email,
                        "phone"     => $this->faker->phoneNumber,
                        "address"   => $this->faker->address,
                        "zipCode"   => $this->faker->postcode,
                        "city"      => $this->faker->city,
                        "state"     => "CA",
                        "country"   => "CA"
                    ],
                ],
            ],
            "billerFields" => $billerFields
        ];

        $this->updateRebillPayload = $updateBillerFields + [
                'newRebill' => [
                    'amount'    => $this->faker->randomFloat(2, 1, 100),
                    'start'     => 10,
                    'frequency' => 365
                ]
            ];
    }

    /**
     * @test
     * @return array
     */
    public function successful_create_rocketgate_sale_with_rebill_should_return_201(): array
    {
        $this->newCardSalePayload['rebill'] = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => 365,
            "start"     => 365
        ];

        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardSalePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        $responseData                               = json_decode($this->response->getContent(), true);
        $this->updateRebillPayload['transactionId'] = $responseData['transactionId'];

        return $this->updateRebillPayload;
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_201_when_successful($updateRebillPayload): array
    {
        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_status_approved($response)
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_a_transaction_id($response)
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_customer_id_is_provided($updateRebillPayload): array
    {
        $updateRebillPayload['merchantCustomerId'] = 'invalid';
        $response                                  = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_400_when_invalid_customer_id_is_provided
     * @param array $response Response
     * @return void
     */
    public function it_should_have_code_20005_when_invalid_customer_id_is_provided($response)
    {
        $this->assertEquals(20005, $response['code']);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_invoice_id_is_provided($updateRebillPayload): array
    {
        $updateRebillPayload['merchantCustomerId'] = 'invalid';
        $response                                  = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_400_when_invalid_invoice_id_is_provided
     * @param array $response Response
     * @return void
     */
    public function it_should_have_code_20005_when_invalid_invoice_id_is_provided($response)
    {
        $this->assertEquals(20005, $response['code']);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_merchant_id_is_provided($updateRebillPayload): array
    {
        $updateRebillPayload['merchantId'] = 'invalid';
        $response                          = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_400_when_invalid_merchant_id_is_provided
     * @param array $response Response
     * @return void
     */
    public function it_should_have_code_406_when_invalid_merchant_id_is_provided($response)
    {
        $this->assertEquals('406', $response['code']);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_merchant_password_is_provided($updateRebillPayload): array
    {
        $updateRebillPayload['merchantPassword'] = 'invalid';
        $response                                = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_400_when_invalid_merchant_password_is_provided
     * @param array $response Response
     * @return void
     */
    public function it_should_have_code_411_when_invalid_merchant_password_is_provided($response)
    {
        $this->assertEquals('411', $response['code']);
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
        $response->assertResponseStatus(\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $updateRebillPayload Update Rebill Payload
     * @return array
     */
    public function it_should_return_201_when_declined($updateRebillPayload): array
    {
        $updateRebillPayload += [
            'amount'   => 0.01,
            'currency' => 'USD',
            'payment'  => [
                'method'      => 'cc',
                'information' => [
                    'number'          => $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
                    'expirationMonth' => $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
                    'expirationYear'  => $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
                    'cvv'             => '111'
                ]
            ]
        ];

        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $updateRebillPayload
        );

        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function decline_sale_response_should_contain_decline_error_classification(array $responseContent): void
    {
        self::assertArrayHasKey('errorClassification', $responseContent);
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function decline_sale_response_should_contain_decline_mapping_criteria(array $responseContent): void
    {
        self::assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_group_decline(array $responseContent): void
    {
        self::assertArrayHasKey('groupDecline', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE,
            $responseContent['errorClassification']['groupDecline']
        );
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_errorType(array $responseContent): void
    {
        self::assertArrayHasKey('errorType', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_ERROR_TYPE,
            $responseContent['errorClassification']['errorType']
        );
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_groupMessage(array $responseContent): void
    {
        self::assertArrayHasKey('groupMessage', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_GROUP_MESSAGE,
            $responseContent['errorClassification']['groupMessage']
        );
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_recommendedAction(array $responseContent): void
    {
        self::assertArrayHasKey('recommendedAction', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION,
            $responseContent['errorClassification']['recommendedAction']
        );
    }

    /**
     * @test
     * @depends it_should_return_201_when_declined
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_mappingCriteria(array $responseContent): void
    {
        self::assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }
}
