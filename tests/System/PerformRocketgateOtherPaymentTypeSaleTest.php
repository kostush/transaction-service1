<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\MemberProfile\Infrastructure\Domain\Model\MemberLogEntry;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\PaymentMethod;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

/**
 * @group otherPayment
 * Class PerformRocketgateOtherPaymentTypeSaleTest
 * @package Tests\System
 */
class PerformRocketgateOtherPaymentTypeSaleTest extends SystemTestCase
{
    /**
     * @var TransactionRepository
     */
    private $repository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(TransactionRepository::class);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_create_a_transaction(): array
    {
        $response = $this->json('POST', $this->uri(), $this->payload());
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     * @param array $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_be_approved(array $responseContent): void
    {
        $this->assertEquals('approved', $responseContent['status']);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     * @param array $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_id(array $responseContent): void
    {
        $this->assertArrayHasKey('transactionId', $responseContent);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     * @param array $responseContent The array key json
     * @return void
     */
    public function successful_create_rocketgate_sale_response_should_contain_an_status(array $responseContent): void
    {
        $this->assertArrayHasKey('status', $responseContent);
    }

    /**
     * @test
     * @return void
     */
    public function successful_create_rocketgate_sale__with_check_payment_with_rebill_should_return_201()
    {
        $payload           = $this->payload();
        $payload['rebill'] = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => $this->faker->numberBetween(1, 100),
            "start"     => $this->faker->numberBetween(1, 100)
        ];

        $response = $this->json('POST', $this->newSaleUrl, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_create_a_declined_transaction_with_a_wrong_merchant_password(): void
    {
        $payload                                     = $this->payload();
        $payload['billerFields']['merchantPassword'] = 'wrong_password';

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $response = json_decode($response->response->getContent(), true);

        $this->assertEquals('declined', $response['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_decline_transaction_on_rc_when_country_code_is_other_than_US(): void
    {
        $payload                                                = $this->payload();
        $payload['payment']['information']['member']['country'] = 'CA';

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $response = json_decode($response->response->getContent(), true);

        $this->assertEquals('declined', $response['status']);
        $this->assertEquals('422', $response['code']);
    }


    /**
     * @test
     * @return void
     */
    public function it_should_return_bad_request_when_member_information_is_not_passed(): void
    {
        $payload = $this->payload();
        unset($payload['payment']['information']['member']);

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     *
     * @param array $responseContent Response Content
     *
     * @return array
     */
    public function it_should_retrieve_other_payment_transaction(array $responseContent): array
    {
        $response = $this->json('GET', $this->retrieveUri($responseContent['transactionId']));
        $response->assertResponseStatus(Response::HTTP_OK);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_retrieve_other_payment_transaction
     * @param array $responseContent Response Content
     * @return void
     */
    public function successful_retrieval_response_should_contain_an_routing_number(array $responseContent): void
    {
        $this->assertArrayHasKey('routing_number', $responseContent);
    }

    /**
     * @test
     * @depends it_should_retrieve_other_payment_transaction
     * @param array $responseContent Response Content
     * @return void
     */
    public function successful_retrieval_response_should_contain_an_account_number(array $responseContent): void
    {
        $this->assertArrayHasKey('account_number', $responseContent);
    }

    /**
     * @test
     * @depends it_should_retrieve_other_payment_transaction
     * @param array $responseContent Response Content
     * @return void
     */
    public function successful_retrieval_response_should_contain_an_social_security_last4(array $responseContent): void
    {
        $this->assertArrayHasKey('social_security_last4', $responseContent);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_create_an_approved_transaction_only_with_merchant_id_and_password(): void
    {
        $payload = $this->payload();
        unset($payload['billerFields']);

        $payload['billerFields'] = [
            "merchantId"       => $this->payload()['billerFields']["merchantId"],
            "merchantPassword" => $this->payload()['billerFields']["merchantPassword"],
        ];

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        $responseContent = json_decode($response->response->getContent(), true);
        $this->assertEquals('approved', $responseContent['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_bad_request_if_payment_method_different_than_checks(): void
    {
        $payload                      = $this->payload();
        $payload['payment']['method'] = 'other_type';

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @param array $rebill Rebill
     * @return void
     * @dataProvider invalid_rebill_fields
     */
    public function it_should_throw_exception_when_invalid_rebill_fields(array $rebill): void
    {
        $payload           = $this->payload();
        $payload['rebill'] = $rebill;

        $response = $this->json('POST', $this->uri(), $payload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function invalid_rebill_fields(): array
    {
        return [
            'boolean_amount'    => [[
                                        'amount'    => true,
                                        'frequency' => 1,
                                        'start'     => 2
                                    ]],
            'boolean_frequency' => [[
                                        'amount'    => 1,
                                        'frequency' => true,
                                        'start'     => 2
                                    ]],
            'boolean_start'     => [[
                                        'amount'    => 1,
                                        'frequency' => 2,
                                        'start'     => true
                                    ]],
            'float_start'       => [[
                                        'amount'    => 1,
                                        'frequency' => 2,
                                        'start'     => 2.2
                                    ]],
            'float_frequency'   => [[
                                        'amount'    => 1,
                                        'frequency' => 2.2,
                                        'start'     => 2
                                    ]],
            'weird_amount'      => [[
                                        'amount'    => "1.000,2",
                                        'frequency' => 2.2,
                                        'start'     => 2
                                    ]],
        ];
    }

    /**
     * @return string
     */
    private function uri(): string
    {
        $sessionId = $this->faker->uuid;
        return '/api/v1/sale/otherPaymentType/rocketgate/session/' . $sessionId;
    }

    /**
     * @param string $transactionId Transaction Id.
     *
     * @return string
     */
    private function retrieveUri(string $transactionId): string
    {
        $sessionId = $this->faker->uuid;
        return '/api/v1/transaction/' . $transactionId . '/session/' . $sessionId;
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     * @param array $responseContent Response
     * @return void
     */
    public function it_should_store_payment_information_obfuscated(array $responseContent): void
    {
        $transaction = $this->repository->findById($responseContent['transactionId']);

        /** @var CheckInformation $checkInformation */
        $checkInformation = $transaction->paymentInformation();
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $checkInformation->routingNumber());
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $checkInformation->accountNumber());
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $checkInformation->socialSecurityLast4());
    }

    /**
     * @test
     * @depends it_should_create_a_transaction
     * @param array $responseContent Response
     * @return void
     */
    public function it_should_store_biller_interaction_payment_information_obfuscated(array $responseContent): void
    {
        $transaction = $this->repository->findById($responseContent['transactionId']);

        /** @var BillerInteraction $billerInteractionRequest */
        $billerInteractionRequest = $transaction->billerInteractions()[1];
        if ($billerInteractionRequest->type() !== BillerInteraction::TYPE_REQUEST) {
            $billerInteractionRequest = $transaction->billerInteractions()[0];
        }

        $billerInteractionRequestPayload = json_decode($billerInteractionRequest->payload(), true);

        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $billerInteractionRequestPayload['routingNo']);
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $billerInteractionRequestPayload['ssNumber']);
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $billerInteractionRequestPayload['accountNo']);
    }

    /**
     * @return array
     */
    private function payload(): array
    {
        $merchantSiteId = $this->faker->numberBetween(1, 100);

        return [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "type"        => PaymentType::CHECKS,
                "method"      => PaymentMethod::CHECKS,
                "information" => [
                    "routingNumber"       => '999999999',
                    "accountNumber"       => "112233",
                    "savingAccount"       => false,
                    "socialSecurityLast4" => $_ENV['ROCKETGATE_CARD_LAST_FOUR_2'],
                    "member"          => [
                        "firstName" => $this->faker->name,
                        "lastName"  => $this->faker->lastName,
                        "email"     => $this->faker->email,
                        "phone"     => $this->faker->phoneNumber,
                        "address"   => $this->faker->address,
                        "zipCode"   => $this->faker->postcode,
                        "city"      => $this->faker->city,
                        "state"     => $this->faker->state,
                        "country"   => 'US'
                    ],
                ],
            ],
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_4'], //it should be RK credentials
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_4'],
                "merchantSiteId"     => (string) $merchantSiteId,
                "merchantAccount"    => "10",
                "merchantProductId"  => $this->faker->uuid,
                "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
                "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true),
                "ipAddress"          => $this->faker->ipv4
            ],
        ];
    }
}
