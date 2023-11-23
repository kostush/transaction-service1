<?php

declare(strict_types=1);

namespace Tests\System;

use Illuminate\Http\Response;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerTransaction;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use Tests\CreatesTransactionData;
use Tests\SystemTestCase;

class PerformRetrieveTransactionTest extends SystemTestCase
{
    use CreatesTransactionData;

    /**
     * @var string
     */
    private $retrieveTransactionUri = '/api/v1/transaction';

    /**
     * @test
     * @return array
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function retrieve_new_credit_card_transaction_should_return_200(): array
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge(['threedsVersion' => 1]);

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        return [$response->response->getContent(), $transaction];
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_correct_transaction_id($array): void
    {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content);

        $this->assertEquals($transactionPayload->transaction->transaction_id, (string) $transaction->transactionId());
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return array
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_biller_transactions(
        $array
    ): array {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content);

        $this->assertIsArray($transactionPayload->biller_transactions);

        return $transactionPayload->biller_transactions;
    }

    /**
     * @test
     * @depends new_credit_card_retrieve_should_return_a_json_response_containing_the_biller_transactions
     * @param array $array The array key json
     * @return void
     */
    public function the_biller_transactions_array_should_not_be_empty(array $array): void
    {
        $this->assertGreaterThan(0, count($array));
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_secured_with_3ds_flag(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content, true);

        $this->assertArrayHasKey('secured_with_three_d', $transactionPayload);
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_true_secured_with_3ds_flag(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content, true);

        $this->assertTrue($transactionPayload['secured_with_three_d']);
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_threeD_secured_version($array): void
    {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content, true);

        $this->assertArrayHasKey('threed_secured_version', $transactionPayload);
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_threeD_secured_version_1($array): void
    {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content, true);

        $this->assertEquals(1, $transactionPayload['threed_secured_version']);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_threeD_secured_version_1_from_transaction_entity(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();
        $transaction->updateThreedsVersion(1);
        $transaction->billerInteractions()->clear();
        $transaction->billerInteractions()->add($this->createBillerInteraction());
        $transaction->billerInteractions()->add(
            $this->createBillerInteraction(
                [
                    'type'      => 'response',
                    'payload'   => json_encode(
                        [
                            "reasonCode"         => "202",
                            "responseCode"       => "0"
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    'createdAt' => new \DateTimeImmutable()
                ]
            )
        );

        $transaction->billerInteractions()->add($this->createBillerInteraction());
        $transaction->billerInteractions()->add(
            $this->createBillerInteraction(
                [
                    'type'      => 'response',
                    'payload'   => json_encode(
                        [
                            "reasonCode"         => "0",
                            "responseCode"       => "0"
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    'createdAt' => new \DateTimeImmutable()
                ]
            )
        );

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        $content = $response->response->getContent();

        $transactionPayload = json_decode($content, true);

        $this->assertEquals(1, $transactionPayload['threed_secured_version']);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function new_credit_card_retrieve_should_return_a_json_response_containing_the_threeD_secured_version_2_from_transaction_entity(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();
        $transaction->updateThreedsVersion(2);
        $transaction->billerInteractions()->clear();
        $transaction->billerInteractions()->add($this->createBillerInteraction());
        $transaction->billerInteractions()->add(
            $this->createBillerInteraction(
                [
                    'type'      => 'response',
                    'payload'   => json_encode(
                        [
                            "reasonCode"         => "225",
                            "responseCode"       => "0"
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    'createdAt' => new \DateTimeImmutable()
                ]
            )
        );

        $transaction->billerInteractions()->add($this->createBillerInteraction());
        $transaction->billerInteractions()->add(
            $this->createBillerInteraction(
                [
                    'type'      => 'response',
                    'payload'   => json_encode(
                        [
                            "reasonCode"         => "0",
                            "responseCode"       => "0"
                        ],
                        JSON_THROW_ON_ERROR
                    ),
                    'createdAt' => new \DateTimeImmutable()
                ]
            )
        );

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        $content = $response->response->getContent();

        $transactionPayload = json_decode($content, true);

        $this->assertEquals(2, $transactionPayload['threed_secured_version']);
    }

    /**
     * @test
     * @return array
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidCreditCardExpirationDateException
     */
    public function retrieve_existing_credit_card_transaction_should_return_200(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        return [$response->response->getContent(), $transaction];
    }

    /**
     * @test
     * @depends retrieve_existing_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function existing_credit_card_retrieve_should_return_a_json_response_containing_the_correct_transaction_id(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content);

        $this->assertEquals($transactionPayload->transaction->transaction_id, (string) $transaction->transactionId());
    }

    /**
     * @test
     * @depends retrieve_existing_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function existing_credit_card_retrieve_should_return_a_json_response_containing_the_payment_template_flag(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content);

        $this->assertTrue($transactionPayload->transaction->payment_template_used);
    }

    /**
     * @test
     * @depends retrieve_existing_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function existing_credit_card_retrieve_should_return_a_json_response_containing_the_biller_transactions(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content);

        $this->assertIsArray($transactionPayload->biller_transactions);
    }

    /**
     * @test
     * @depends retrieve_existing_credit_card_transaction_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function existing_credit_card_retrieve_should_return_a_json_response_containing_the_secured_with_3ds_flag(
        $array
    ): void {
        [$content, $transaction] = $array;

        $transactionPayload = json_decode($content, true);

        $this->assertArrayHasKey('secured_with_three_d', $transactionPayload);
    }

    /**
     * @test
     * @return void
     */
    public function retrieve_should_return_400_when_invalid_uuid_given_for_transaction_id(): void
    {
        $response = $this->get($this->retrieveTransactionUri . '/' . 'invalid-transaction-uuid' . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @return array
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function retrieve_new_credit_card_free_sale_transaction_should_return_200(): array
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge(['amount' => '1.02']);

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        return [$response->response->getContent(), $transaction];
    }

    /**
     * @test
     * @return array
     */
    public function retrieve_rocketgate_failed_new_credit_card_sale_transaction_should_return_a_201_response(): array
    {
        $this->minPayload['amount']         = 0.02;
        $this->minPayload['isNSFSupported'] = true;

        $response = $this->json('POST', $this->newSaleUrl, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $response = json_decode($response->response->getContent());

        $result = $this->get($this->retrieveTransactionUri . '/' . (string) $response->transactionId . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $result->assertResponseStatus(Response::HTTP_OK);

        return json_decode($result->response->getContent(), true);
    }

    /**
     * @test
     * @depends retrieve_rocketgate_failed_new_credit_card_sale_transaction_should_return_a_201_response
     * @param array $response The response array
     */
    public function retrieve_rocketgate_failed_new_credit_card_sale_transaction_should_return_the_correct_decline_reason(array $response)
    {
        $this->assertEquals($response['transaction']['code'], RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT);
    }

    /**
     * @test
     * @depends retrieve_rocketgate_failed_new_credit_card_sale_transaction_should_return_a_201_response
     * @param array $response The response array
     */
    public function retrieve_rocketgate_nsf_transaction_should_have_a_card_upload_biller_transaction(array $response)
    {
        $this->assertEquals($response['biller_transactions'][1]['type'], RocketgateBillerTransaction::CARD_UPLOAD_TYPE);
    }

    /**
     * @test
     * @return void
     */
    public function retrieve_RG_NSF_new_cc_transaction_for_site_with_NSF_enabled_should_have_card_upload(): void
    {
        $this->minPayload['amount']         = 0.02;
        $this->minPayload['isNSFSupported'] = true;

        $response = $this->json('POST', $this->newSaleUrl, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $response = json_decode($response->response->getContent());

        $result = $this->get($this->retrieveTransactionUri . '/' . (string) $response->transactionId . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $result->assertResponseStatus(Response::HTTP_OK);

        $response = json_decode($result->response->getContent(), true);
        $this->assertEquals($response['biller_transactions'][1]['type'], RocketgateBillerTransaction::CARD_UPLOAD_TYPE);
    }

    /**
     * @test
     * @return void
     */
    public function retrieve_RG_NSF_new_cc_transaction_for_site_with_NSF_disabled_should_not_have_card_upload(): void
    {
        $this->minPayload['amount']         = 0.02;
        $this->minPayload['isNSFSupported'] = false;

        $response = $this->json('POST', $this->newSaleUrl, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        $response = json_decode($response->response->getContent());

        $result = $this->get($this->retrieveTransactionUri . '/' . (string) $response->transactionId . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $result->assertResponseStatus(Response::HTTP_OK);

        $response = json_decode($result->response->getContent(), true);

        foreach ($response['biller_transactions'] as $biller_transaction_type) {
            $this->assertNotEquals($biller_transaction_type['type'], RocketgateBillerTransaction::CARD_UPLOAD_TYPE);
        }
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_contain_merchant_site_id_as_an_empty_string(): void
    {
        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $this->faker->uuid,
            $this->faker->numberBetween(0, 20),
            BillerSettings::ROCKETGATE,
            $this->faker->currencyCode,
            $this->createNewCreditCardCommandPayment(),
            RocketGateChargeSettings::create(
                'merchantId',
                'merchantPassword',
                null,
                null,
                null,
                '', // this is the merchantSiteId
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        );

        /** @var TransactionRepository $repository */
        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->retrieveTransactionUri . '/' . $transaction->transactionId() . '/session/' . $this->faker->uuid);

        $billerSettings = json_decode($response->response->getContent(),true)['biller_settings'];

        $this->assertSame('', $billerSettings['merchant_site_id']);
    }
}