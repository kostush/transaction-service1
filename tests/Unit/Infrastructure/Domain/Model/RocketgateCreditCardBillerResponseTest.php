<?php

declare(strict_types=1);

namespace Tests\Unit\Infastructure\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\UnitTestCase;

class RocketgateCreditCardBillerResponseTest extends UnitTestCase
{
    /**
     * @test
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    public function it_should_return_a_valid_rocketgate_credit_card_biller_response(): RocketgateCreditCardBillerResponse
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => [
                        'version'            => 'P6.6m',
                        'cvv2Check'          => 'TRUE',
                        'billingType'        => 'I',
                        'amount'             => '10.02',
                        'currency'           => 'USD',
                        'cardNo'             => $this->faker->creditCardNumber,
                        'expireMonth'        => $this->faker->month,
                        'expireYear'         => $this->faker->year,
                        'cvv2'               => '123',
                        'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                        'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                        'use3DSecure'        => 'FALSE',
                        'rebillAmount'       => '10.03',
                        'rebillFrequency'    => 30,
                        'rebillStart'        => 5,
                        'merchantCustomerID' => 'c2d9bf96-5fb7afca466a68.63383504',
                        'merchantInvoiceID'  => '06991d18-5fb7afca466c44.63589119',
                        'transactionType'    => 'CC_CONFIRM',
                        'referenceGUID'      => '1000175E586B621',
                    ],
                    'response' => [
                        'authNo'             => '257502',
                        'merchantInvoiceID'  => '06991d18-5fb7afca466c44.63589119',
                        'merchantAccount'    => '87',
                        'approvedAmount'     => '10.02',
                        'cardLastFour'       => '1117',
                        'version'            => '1.0',
                        'merchantCustomerID' => 'c2d9bf96-5fb7afca466a68.63383504',
                        'scrubResults'       => 'NEGDB=0,PROFILE=0,ACTIVITY=0',
                        'reasonCode'         => '0',
                        'transactionTime'    => '2020-11-20 07:00:12',
                        'retrievalNo'        => '1000175e586b621',
                        'payType'            => 'CREDIT',
                        'balanceAmount'      => '10.02',
                        'balanceCurrency'    => 'USD',
                        'cardHash'           => $_ENV['ROCKETGATE_CARD_HASH_2'],
                        'cardDebitCredit'    => '0',
                        'cardDescription'    => 'UNKNOWN',
                        'cardType'           => 'VISA',
                        'bankResponseCode'   => '0',
                        'approvedCurrency'   => 'USD',
                        'guidNo'             => '1000175E586B621',
                        'cardExpiration'     => '0123',
                        'responseCode'       => '0',
                    ],
                    'code'     => '0',
                    'reason'   => '0'
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(RocketgateCreditCardBillerResponse::class, $billerResponse);

        return $billerResponse;
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_a_valid_request_json(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertJson($billerResponse->requestPayload());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_a_valid_response_json(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertJson($billerResponse->responsePayload());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_correct_biller_transaction_id(
        RocketgateCreditCardBillerResponse $billerResponse
    ): void {
        $this->assertSame('1000175E586B621', $billerResponse->billerTransactionId());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_correct_balance_amount(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertSame(10.02, $billerResponse->balanceAmount());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_correct_balance_currency(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertSame('USD', $billerResponse->balanceCurrency());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_not_return_400(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertFalse($billerResponse->shouldReturn400());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_not_retry_without_three_d(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertFalse($billerResponse->shouldRetryWithoutThreeD());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     * @throws \JsonException
     */
    public function it_should_not_retry_with_three_d(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertFalse($billerResponse->shouldRetryWithThreeD());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_false_for_nsf_transction(RocketgateCreditCardBillerResponse $billerResponse): void
    {
        $this->assertFalse($billerResponse->isNsfTransaction());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_false_for_three_ds_auth_required(
        RocketgateCreditCardBillerResponse $billerResponse
    ): void {
        $this->assertFalse($billerResponse->threeDsAuthIsRequired());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function it_should_return_false_for_three_ds_init_required(
        RocketgateCreditCardBillerResponse $billerResponse
    ): void {
        $this->assertFalse($billerResponse->threeDsInitIsRequired());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_rocketgate_credit_card_biller_response
     * @param RocketgateCreditCardBillerResponse $billerResponse Biller Response
     * @return void
     * @throws \JsonException
     */
    public function it_should_return_null_if_three_ds_version_is_requested(
        RocketgateCreditCardBillerResponse $billerResponse
    ): void {
        $this->assertNull($billerResponse->threedsVersion());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_three_ds_version_one(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => 'TRUE'
                    ],
                    'response' => [
                        'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'responseCode' => '2',
                        'PAREQ'        => 'SimulatedPAREQ1000175E0A055C9',
                        'acsURL'       => $this->faker->url
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertSame(Transaction::THREE_DS_ONE, $billerResponse->threedsVersion());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_three_ds_version_one_when_rocketgate_responds_with_the_three_ds_version_one(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => 'TRUE'
                    ],
                    'response' => [
                        'reasonCode'        => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'responseCode'      => '2',
                        '_3DSECURE_VERSION' => '1.2.3',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertSame(Transaction::THREE_DS_ONE, $billerResponse->threedsVersion());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_three_ds_version_two(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => 'TRUE'
                    ],
                    'response' => [
                        'reasonCode'                      => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                        'responseCode'                    => '2',
                        '_3DSECURE_DEVICE_COLLECTION_JWT' => 'jwt',
                        '_3DSECURE_DEVICE_COLLECTION_URL' => $this->faker->url
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertSame(Transaction::THREE_DS_TWO, $billerResponse->threedsVersion());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_three_ds_version_two_when_rocketgate_responds_with_the_three_ds_version_two(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => [
                        'request'     => 'json',
                        'use3DSecure' => 'TRUE'
                    ],
                    'response' => [
                        'reasonCode'        => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'responseCode'      => '2',
                        '_3DSECURE_VERSION' => '2.3.4',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertSame(Transaction::THREE_DS_TWO, $billerResponse->threedsVersion());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_retry_with_three_ds_one(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ['request' => 'json'],
                    'response' => [
                        'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'responseCode' => '2',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertTrue($billerResponse->shouldRetryWithThreeD());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_not_retry_with_three_ds_when_sec_rev_and_simplified_three_ds_is_disabled(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ['cardHash' => 'hash'],
                    'response' => [
                        'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                        'responseCode' => '2',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        self::assertFalse($billerResponse->shouldRetryWithThreeD(false));
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_retry_with_three_ds_when_sec_rev_and_simplified_three_ds_is_enabled(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ['cardHash' => 'hash'],
                    'response' => [
                        'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                        'responseCode' => '2',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        self::assertTrue($billerResponse->shouldRetryWithThreeD(true));
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_retry_with_three_ds_two(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ['request' => 'json'],
                    'response' => [
                        'reasonCode'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                        'responseCode' => '2',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS2_INITIATION,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertTrue($billerResponse->shouldRetryWithThreeD());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_retry_with_three_ds_two_even_if_3ds_auth_code_is_received_but_version_on_rocketgate_response_is_two(): void
    {
        $billerResponse = RocketgateCreditCardBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'request'  => ['request' => 'json'],
                    'response' => [
                        'reasonCode'        => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'responseCode'      => '2',
                        'PAREQ'             => 'SimulatedPAREQ1000175E0A055C9',
                        'acsURL'            => $this->faker->url,
                        '_3DSECURE_VERSION' => '2.3.4',
                    ],
                    'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'code'     => '2',
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertTrue($billerResponse->shouldRetryWithThreeD());
    }
}
