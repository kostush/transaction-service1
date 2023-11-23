<?php
declare(strict_types=1);

namespace Tests\System;

use ProBillerNG\Transaction\Domain\Model\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class CompleteThreeDTest extends SystemTestCase
{
    private const MAX_NUMBER_TRIES = 3;

    /**
     * @var string
     */
    private $newCardSaleUri = '/api/v1/sale/newCard/rocketgate/session/bdeee6e5-5d45-4c0b-9511-d86c654dd77f';

    /**
     * @var string
     */
    private $lookupUri = '/api/v1/threeds-lookup/rocketgate/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var string
     */
    private $completeThreeDUri = '/api/v1/transaction/{transactionId}/rocketgate/completeThreeD/session/bdeee6e5-5d45-4c0b-9511-d86c654dd77f';

    /**
     * @var array
     */
    private $newCardWithThreeDPayload;

    /**
     * @var array
     */
    protected $lookupPayload;

    /**
     * @var array
     */
    protected $binIntelligencePayload;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $merchantSiteId = $this->faker->numberBetween(1, 100);

        $this->newCardWithThreeDPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "useThreeD"    => true,
            'rebill'       => [
                'amount'    => $this->faker->randomFloat(2, 1, 100),
                'start'     => 10,
                'frequency' => 365
            ],
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
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                "merchantSiteId"     => (string) $merchantSiteId,
                "merchantProductId"  => $this->faker->uuid,
                "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
                "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true),
                "ipAddress"          => $this->faker->ipv4,
                "sharedSecret"       => $this->faker->word,
                "simplified3DS"      => false
            ],
        ];

        $this->binIntelligencePayload = [
            'siteId'       => $this->faker->uuid,
            'amount'       => $this->faker->randomFloat(2, 1, 100),
            'currency'     => 'USD',
            'useThreeD'    => true,
            'payment'      => [
                'method'      => 'cc',
                'information' => [
                    'number'          => $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_STEP_UP'],
                    'expirationMonth' => 1,
                    'expirationYear'  => 2023,
                    'cvv'             => 123
                ],
            ],
            'billerFields' => [
                // params that force 3DS2 scenario
                'merchantId'       => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantPassword' => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
            ],
        ];

        $this->lookupPayload = [
            'deviceFingerprintingId' => 'fake',
            'previousTransactionId'  => '94f05af5-6c67-4558-bbaf-b86a7b1177f6',
            'redirectUrl'            => 'fake',
            'payment'                => [
                'method'      => 'cc',
                'information' => [
                    'number'          => $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_STEP_UP'],
                    'expirationMonth' => $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
                    'expirationYear'  => $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
                    'cvv'             => $_ENV['ROCKETGATE_CARD_CVV_1']
                ]
            ]
        ];
    }

    /**
     * @test
     * @return array
     */
    public function new_sale_should_return_201_when_use_threeD_is_provided(): array
    {
        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardWithThreeDPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return json_decode($this->response->getContent(), true);
    }

    /**
     * @test
     * @depends new_sale_should_return_201_when_use_threeD_is_provided
     * @param array $responseData New Sale Response
     * @return void
     */
    public function new_sale_should_return_a_transaction_id($responseData): void
    {
        $this->assertNotEmpty($responseData['transactionId']);
    }

    /**
     * @test
     * @depends new_sale_should_return_201_when_use_threeD_is_provided
     * @param array $responseData New Sale Response
     * @return void
     */
    public function new_sale_should_return_pareq($responseData): void
    {
        $this->assertNotEmpty($responseData['pareq']);
    }

    /**
     * @test
     * @depends new_sale_should_return_201_when_use_threeD_is_provided
     * @param array $responseData New Sale Response
     * @return array
     */
    public function it_should_return_200_when_correct_pares_is_provided($responseData): array
    {
        $payload['pares'] = str_replace(
            'PAREQ',
            'PARES',
            $responseData['pareq']
        );

        $completeThreeDUri = str_replace(
            '{transactionId}',
            $responseData['transactionId'],
            $this->completeThreeDUri
        );

        $response = $this->json('PUT', $completeThreeDUri, $payload);
        $response->assertResponseStatus(Response::HTTP_OK);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_200_when_correct_pares_is_provided
     * @param array $responseData Complete ThreeD Response
     * @return void
     */
    public function it_should_return_status_approved_when_correct_pares_is_provided($responseData): void
    {
        $this->assertSame('approved', $responseData['status']);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_when_correct_md_is_provided(): array
    {
        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['previousTransactionId'] = $binIntelligenceResponse['transactionId'];

        $lookupResponse = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $lookupResponse = json_decode($lookupResponse->getContent(), true);

        $completeThreeDUri = str_replace(
            '{transactionId}',
            $lookupResponse['transactionId'],
            $this->completeThreeDUri
        );

        $response = $this->json(
            'PUT',
            $completeThreeDUri,
            ['md' => $lookupResponse['threeD']['md']]
        );

        $response->assertResponseStatus(Response::HTTP_OK);

        return [
            'transactionId' => $lookupResponse['transactionId'],
            'response'      => json_decode($response->response->getContent(), true)
        ];
    }

    /**
     * @test
     * @depends it_should_return_200_when_correct_md_is_provided
     * @param array $responseData Complete response
     * @return void
     */
    public function it_should_return_correct_transaction_id($responseData): void
    {
        $this->assertSame($responseData['transactionId'], $responseData['response']['transactionId']);
    }

    /**
     * @test
     * @depends it_should_return_200_when_correct_md_is_provided
     * @param array $responseData Complete response
     * @return void
     */
    public function it_should_return_approved_status($responseData): void
    {
        $this->assertSame('approved', $responseData['response']['status']);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_when_incorrect_md_is_provided(): array
    {
        $newSaleResponse = $this->json(
            'POST',
            $this->newCardSaleUri,
            $this->newCardWithThreeDPayload
        );

        $decodedNewSaleResponse = json_decode($newSaleResponse->response->getContent(), true);

        $completeThreeDUri = str_replace(
            '{transactionId}',
            $decodedNewSaleResponse['transactionId'],
            $this->completeThreeDUri
        );

        $response = $this->json(
            'PUT',
            $completeThreeDUri,
            ['md' => '10001732904A027']
        );

        $response->assertResponseStatus(Response::HTTP_OK);

        return [
            'transactionId' => $decodedNewSaleResponse['transactionId'],
            'response'      => json_decode($response->response->getContent(), true)
        ];
    }

    /**
     * @test
     * @depends it_should_return_200_when_incorrect_md_is_provided
     * @param array $responseData Complete response
     * @return void
     */
    public function it_should_return_correct_transaction_id_if_transaction_was_declined($responseData): void
    {
        $this->assertSame($responseData['transactionId'], $responseData['response']['transactionId']);
    }

    /**
     * @test
     * @depends it_should_return_200_when_incorrect_md_is_provided
     * @param array $responseData Complete response
     * @return void
     */
    public function it_should_return_declined_status($responseData): void
    {
        $this->assertSame('declined', $responseData['response']['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_404_when_even_if_md_has_wrong_type_and_transaction_is_not_found(): void
    {
        $completeThreeDUri = str_replace(
            '{transactionId}',
            $this->faker->uuid,
            $this->completeThreeDUri
        );

        $response = $this->json(
            'PUT',
            $completeThreeDUri,
            ['md' => true]
        );

        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @depends new_sale_should_return_201_when_use_threeD_is_provided
     * @param array $responseData New Sale Response
     * @return void
     */
    public function it_should_return_404_when_incorrect_transaction_id_is_provided($responseData): void
    {
        $payload['pares'] = str_replace(
            'PAREQ',
            'PARES',
            $responseData['pareq']
        );

        $completeThreeDUri = str_replace(
            '{transactionId}',
            $this->faker->uuid,
            $this->completeThreeDUri
        );

        $response = $this->json('PUT', $completeThreeDUri, $payload);
        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @return array
     */
    public function second_sale_should_return_201_when_use_threeD_is_provided(): array
    {
        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardWithThreeDPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($this->response->getContent(), true);
    }

    /**
     * @test
     * @depends second_sale_should_return_201_when_use_threeD_is_provided
     * @param array $responseData New Sale Response
     * @return void
     */
    public function it_should_return_status_declined_when_incorrect_pares_is_provided($responseData): void
    {
        $payload['pares'] = str_replace(
            'PAREQ',
            'INVALID',
            $responseData['pareq']
        );

        $completeThreeDUri = str_replace(
            '{transactionId}',
            $responseData['transactionId'],
            $this->completeThreeDUri
        );

        $response     = $this->json('PUT', $completeThreeDUri, $payload);
        $responseData = json_decode($response->response->getContent(), true);
        $this->assertSame('declined', $responseData['status']);
    }

    /**
     * @test
     * @depends it_should_return_200_when_correct_pares_is_provided
     * @param array $response Response
     *
     * @return void
     */
    public function it_should_write_bi_event_Transaction_Updated(array $response): void
    {
        $logFile = storage_path('/logs/' . env('BI_LOG_FILE'));

        $transactionId = $response['transactionId'];

        $logContent = exec("cat $logFile | grep " . $transactionId);

        $this->assertStringContainsString(sprintf('"transactionId":"%s"', $transactionId), $logContent);
        $this->assertStringContainsString('Transaction_Updated', $logContent);
    }

    /**
     * @param array $binIntelligencePayload Bin intelligence payload
     * @return array|null
     */
    private function performBinIntelligenceTransaction(array $binIntelligencePayload): ?array
    {
        // When trying to perform a bin intelligence transaction for 3ds2, Rocketgate either responds
        // with a 3ds1 transaction / 3ds2 transaction / successful purchase directly (because of their load balancer).
        // This happens just on their testing environment. In order for our system tests to pass, we are trying
        // to create a bin intelligence transaction for 3 times. If, after 3 times, Rocketgate did't respond
        // with a 3ds2 transaction, we mark the test as skipped. Otherwise, we should have a passed test.

        $timesTried     = 0;
        $threeDsVersion = 0;

        do {
            if ($timesTried === self::MAX_NUMBER_TRIES) {
                break;
            }

            $binIntelligenceResponse = $this->call('POST', $this->newCardSaleUri, $binIntelligencePayload);
            $binIntelligenceResponse = json_decode(
                $binIntelligenceResponse->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!isset($binIntelligenceResponse['threeD']['version'])) {
                $threeDsVersion = 0;
            } else {
                $threeDsVersion = $binIntelligenceResponse['threeD']['version'];
            }

            $timesTried++;
        } while ($threeDsVersion !== Transaction::THREE_DS_TWO);

        if ($threeDsVersion !== Transaction::THREE_DS_TWO) {
            return null;
        }

        return $binIntelligenceResponse;
    }
}
