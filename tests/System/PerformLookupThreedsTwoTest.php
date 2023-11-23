<?php
declare(strict_types=1);

namespace Tests\System;

use Illuminate\Http\Response;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\SystemTestCase;

class PerformLookupThreedsTwoTest extends SystemTestCase
{
    private const MAX_NUMBER_TRIES = 3;

    /**
     * @var string
     */
    private $newSaleUri = '/api/v1/sale/newCard/rocketgate/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var string
     */
    private $lookupUri = '/api/v1/threeds-lookup/rocketgate/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var array
     */
    protected $binIntelligencePayload;

    /**
     * @var array
     */
    protected $lookupPayload;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->binIntelligencePayload = [
            'siteId'       => $this->faker->uuid,
            'amount'       => $this->faker->randomFloat(2, 1, 100),
            'currency'     => 'USD',
            'useThreeD'    => true,
            'payment'      => [
                'method'      => 'cc',
                'information' => [
                    'number'          => $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_STEP_UP'],
                    'expirationMonth' => $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
                    'expirationYear'  => $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
                    'cvv'             => $_ENV['ROCKETGATE_CARD_CVV_1']
                ],
            ],
            'billerFields' => [
                // params that force 3DS2 scenario
                'merchantId'       => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantPassword' => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'merchantAccount'  => 2
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
    public function it_should_return_200_status_code_when_frictionless(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_FRICTIONLESS'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];
        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_FRICTIONLESS'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_frictionless
     */
    public function it_should_return_an_approved_transaction_when_frictionless(array $response): void
    {
        $this->assertSame($response['status'], 'approved');
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_threeds2_auth_required(): array
    {
        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['previousTransactionId'] = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds2_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_pending_transaction_when_threeds2_auth_required(array $response): void
    {
        $this->assertSame($response['status'], 'pending');
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds2_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_version_two_transaction_when_threeds2_auth_required(array $response): void
    {
        $this->assertSame($response['threeD']['version'], 2);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds2_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_device_collection_url_when_threeds2_auth_required(array $response): void
    {
        $this->assertArrayHasKey('stepUpUrl', $response['threeD']);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds2_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_device_collection_jwt_when_threeds2_auth_required(array $response): void
    {
        $this->assertArrayHasKey('stepUpJwt', $response['threeD']);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds2_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_md_when_threeds2_auth_required(array $response): void
    {
        $this->assertArrayHasKey('md', $response['threeD']);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_threeds1_auth_required(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_AUTH'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_AUTH'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds1_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_pending_transaction_when_threeds1_auth_required(array $response): void
    {
        $this->assertSame($response['status'], 'pending');
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds1_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_version_one_transaction_when_threeds1_auth_required(array $response): void
    {
        $this->assertSame($response['threeD']['version'], 1);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds1_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_pareq_when_threeds1_auth_required(array $response): void
    {
        $this->assertArrayHasKey('pareq', $response['threeD']);
    }

    /**
     * @test
     * @depends it_should_return_200_status_code_when_threeds1_auth_required
     * @param array $response Response
     * @return void
     */
    public function it_should_return_a_threed_acs_url_when_threeds1_auth_required(array $response): void
    {
        $this->assertArrayHasKey('acs', $response['threeD']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_400_bad_request_when_device_fingerprint_id_is_missing(): void
    {
        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['previousTransactionId']  = $binIntelligenceResponse['transactionId'];
        $this->lookupPayload['deviceFingerprintingId'] = null;

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_failed_because_of_bypassed_authentication_223_error_code(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_AUTHBYPASS'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_AUTHBYPASS'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_failed_because_of_bypassed_authentication_223_error_code
     */
    public function it_should_return_an_approved_transaction_when_bypassed_authentication(array $response): void
    {
        $this->assertSame($response['status'], 'approved');
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_failed_because_of_card_not_enrolled_203_error_code(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_NOT_ENROLED'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_NOT_ENROLED'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_failed_because_of_card_not_enrolled_203_error_code
     */
    public function it_should_return_an_approved_transaction_when_card_not_enrolled(array $response): void
    {
        $this->assertSame($response['status'], 'approved');
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_failed_because_threed_secure_ineligible_204_error_code(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_NOT_ELLIGIBLE'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_NOT_ELLIGIBLE'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_failed_because_threed_secure_ineligible_204_error_code
     */
    public function it_should_return_an_approved_transaction_when_threed_secure_ineligible(array $response): void
    {
        $this->assertSame($response['status'], 'approved');
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_failed_because_threed_secure_rejected_205_error_code(): array
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_REJECTED'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_REJECTED'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_failed_because_threed_secure_rejected_205_error_code
     */
    public function it_should_return_an_approved_transaction_when_threed_secure_rejected(array $response): void
    {
        $this->assertSame($response['status'], 'approved');
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

            $binIntelligenceResponse = $this->call('POST', $this->newSaleUri, $binIntelligencePayload);
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

    /**
     * @test
     * @return array
     */
    public function it_should_return_200_status_code_when_failed_because_of_bypassed_3ds_223_error_code_with_nfs(): array
    {
        $this->binIntelligencePayload['amount'] = 0.02;
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_BYPASSED'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS_BYPASSED'];
        $this->lookupPayload['previousTransactionId']            = $binIntelligenceResponse['transactionId'];
        $this->lookupPayload['isNSFSupported']                   = true;

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @test
     * @param array $response Response
     * @return void
     * @depends it_should_return_200_status_code_when_failed_because_of_bypassed_3ds_223_error_code_with_nfs
     */
    public function it_should_return_an_declined_transaction_when_trying_transaction_with_bypassing_3ds_but_get_nfs_declined(array $response): void
    {
        $this->assertSame($response['status'], 'declined');
        $this->assertSame($response['code'], '105');
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_400_status_code_when_previous_transaction_is_aborted(): void
    {
        $this->binIntelligencePayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_FRICTIONLESS'];

        $binIntelligenceResponse = $this->performBinIntelligenceTransaction($this->binIntelligencePayload);

        if ($binIntelligenceResponse === null) {
            $this->markTestSkipped();
        }

        $previousTransactionId = $binIntelligenceResponse['transactionId'];

        $response = $this->put(
            '/api/v1/transaction/' . $previousTransactionId . '/abort',
        );

        $this->lookupPayload['previousTransactionId']            = $previousTransactionId;
        $this->lookupPayload['payment']['information']['number'] = $_ENV['ROCKETGATE_CARD_NUMBER_3DS2_FRICTIONLESS'];

        $response = $this->call(
            'PUT',
            $this->lookupUri,
            $this->lookupPayload
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
