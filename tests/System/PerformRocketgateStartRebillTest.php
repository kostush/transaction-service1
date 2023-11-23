<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class PerformRocketgateStartRebillTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $cancelRebillUri = '/api/v1/cancelRebill/rocketgate/session/4be9d58b-2943-42e2-a022-08247ae4cd17';

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
    private $cancelRebillPayload;

    /**
     * @var array
     */
    private $stopRebillPayload;

    /**
     * @var array
     */
    private $startRebillPayload;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $merchantSiteId = $this->faker->numberBetween(1, 100);

        $this->cancelRebillPayload = [
            "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
            "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
            "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
            "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true)
        ];

        $billerFields = $this->cancelRebillPayload + [
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

        $this->startRebillPayload = $this->cancelRebillPayload + [
                'startRebill' => [
                    'amount'    => $this->faker->randomFloat(2, 1, 100),
                    'start'     => 10,
                    'frequency' => 365
                ]
            ];

        $this->stopRebillPayload = $this->cancelRebillPayload + [
                'stopRebill' => true
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
        $this->cancelRebillPayload['transactionId'] = $responseData['transactionId'];
        $this->startRebillPayload['transactionId']  = $responseData['transactionId'];

        return [
            'cancelRebillPayload' => $this->cancelRebillPayload,
            'startRebillPayload'  => $this->startRebillPayload
        ];
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $payloads Payloads
     * @return array
     */
    public function successful_cancel_rebill_should_return_201($payloads): array
    {
        $response = $this->json(
            'POST',
            $this->cancelRebillUri,
            $payloads['cancelRebillPayload']
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_with_rebill_should_return_201
     * @param array $payloads Payloads
     * @return array
     */
    public function it_should_return_201_when_successful_after_cancel($payloads): array
    {
        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $payloads['startRebillPayload']
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful_after_cancel
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_status_approved_when_successful_after_cancel($response)
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful_after_cancel
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_a_transaction_id_when_successful_after_cancel($response)
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @return array
     */
    public function successful_create_rocketgate_sale_without_rebill_should_return_201(): array
    {
        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardSalePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        $responseData                              = json_decode($this->response->getContent(), true);
        $this->startRebillPayload['transactionId'] = $responseData['transactionId'];

        return $this->startRebillPayload;
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_without_rebill_should_return_201
     * @param array $startRebillPayload Start Rebill Payload
     * @return array
     */
    public function successful_start_after_sale_without_rebill_should_return_201($startRebillPayload): array
    {
        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $startRebillPayload
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends successful_start_after_sale_without_rebill_should_return_201
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_status_approved_when_successful_after_sale_without_rebill($response)
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends successful_start_after_sale_without_rebill_should_return_201
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_a_transaction_id_when_successful_after_sale_without_rebill($response)
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @return array
     */
    public function successful_create_rocketgate_second_sale_with_rebill_should_return_201(): array
    {
        $this->newCardSalePayload['rebill'] = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => 365,
            "start"     => 365
        ];

        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardSalePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        $responseData                              = json_decode($this->response->getContent(), true);
        $this->stopRebillPayload['transactionId']  = $responseData['transactionId'];
        $this->startRebillPayload['transactionId'] = $responseData['transactionId'];

        return [
            'stopRebillPayload'  => $this->stopRebillPayload,
            'startRebillPayload' => $this->startRebillPayload
        ];
    }

    /**
     * @test
     * @depends successful_create_rocketgate_second_sale_with_rebill_should_return_201
     * @param array $payloads Payloads
     * @return array
     */
    public function successful_stop_rebill_should_return_201($payloads): array
    {
        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $payloads['stopRebillPayload']
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_second_sale_with_rebill_should_return_201
     * @param array $payloads Payloads
     * @return array
     */
    public function it_should_return_201_when_successful_after_stop($payloads): array
    {
        $response = $this->json(
            'POST',
            $this->updateRebillUri,
            $payloads['startRebillPayload']
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful_after_stop
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_status_approved_when_successful_after_stop($response)
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_201_when_successful_after_stop
     * @param array $response Response
     * @return void
     */
    public function it_should_contain_a_transaction_id_when_successful_after_stop($response)
    {
        $this->assertArrayHasKey('transactionId', $response);
    }

    /**
     * @test
     * @depends successful_create_rocketgate_sale_without_rebill_should_return_201
     * @param array $startRebillPayload Start Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_customer_id_is_provided($startRebillPayload): array
    {
        $startRebillPayload['merchantCustomerId'] = 'invalid';
        $response                                 = $this->json(
            'POST',
            $this->updateRebillUri,
            $startRebillPayload
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
     * @depends successful_create_rocketgate_sale_without_rebill_should_return_201
     * @param array $startRebillPayload Start Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_invoice_id_is_provided($startRebillPayload): array
    {
        $startRebillPayload['merchantInvoiceId'] = 'invalid';
        $response                                = $this->json(
            'POST',
            $this->updateRebillUri,
            $startRebillPayload
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
     * @depends successful_create_rocketgate_sale_without_rebill_should_return_201
     * @param array $startRebillPayload Start Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_merchant_id_is_provided($startRebillPayload): array
    {
        $startRebillPayload['merchantId'] = 'invalid';
        $response                         = $this->json(
            'POST',
            $this->updateRebillUri,
            $startRebillPayload
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
     * @depends successful_create_rocketgate_sale_without_rebill_should_return_201
     * @param array $startRebillPayload Start Rebill Payload
     * @return array
     */
    public function it_should_return_400_when_invalid_merchant_password_is_provided($startRebillPayload): array
    {
        $startRebillPayload['merchantPassword'] = 'invalid';
        $response                               = $this->json(
            'POST',
            $this->updateRebillUri,
            $startRebillPayload
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
}
