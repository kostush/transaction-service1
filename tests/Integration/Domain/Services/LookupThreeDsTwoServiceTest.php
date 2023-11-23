<?php

namespace Tests\Integration\Domain\Services;

use DateTimeImmutable;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
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
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateLookupThreeDsTwoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\IntegrationTestCase;

class LookupThreeDsTwoServiceTest extends IntegrationTestCase
{
    /**
     * @var LookupThreeDsTwoTranslatingService|MockObject
     */
    private $lookupTranslatingService;

    /**
     * @var ChargeService|MockObject
     */
    private $chargeService;

    /**
     * @var LookupThreeDsTwoService
     */
    private $service;

    /**
     * @var BILoggerService
     */
    private $biService;

    /**
     * Setup test
     * @return void
     */
    public function setUp(): void
    {
        $this->chargeService            = $this->createMock(ChargeService::class);
        $this->lookupTranslatingService = $this->createMock(LookupThreeDsTwoTranslatingService::class);
        $this->biService                = $this->createMock(BILoggerService::class);

        $this->service = new LookupThreeDsTwoService(
            $this->lookupTranslatingService,
            $this->chargeService,
            $this->biService
        );

        parent::setUp();
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidThreedsVersionException
     */
    public function it_should_return_a_transaction_having_threeds_version_two(): void
    {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'           => '0',
                            'response_code'         => '0',
                            'reason_desc'           => 'Success',
                            '_3DSECURE_STEP_UP_URL' => 'url',
                            '_3DSECURE_STEP_UP_JWT' => 'jwt',
                            'guidNo'                => 'billerTransactionId',
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $transactionAfterLookup = $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );

        $this->assertSame($transactionAfterLookup->threedsVersion(), Transaction::THREE_DS_TWO);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_transaction_having_threeds_version_one(): void
    {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success',
                            'PAREQ'         => 'pareq',
                            'acsURL'        => 'url',
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $transactionAfterLookup = $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );

        $this->assertSame($transactionAfterLookup->threedsVersion(), Transaction::THREE_DS_ONE);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_transaction_using_threeds(): void
    {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'           => '0',
                            'response_code'         => '0',
                            'reason_desc'           => 'Success',
                            '_3DSECURE_STEP_UP_URL' => 'url',
                            '_3DSECURE_STEP_UP_JWT' => 'jwt',
                            'guidNo'                => 'billerTransactionId',
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $transaction->updateTransactionWith3D(false);

        $transactionAfterLookup = $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );

        $this->assertTrue($transactionAfterLookup->with3D());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_transaction_not_using_threeds(): void
    {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success',
                            'PAREQ'         => 'pareq',
                            'acsURL'        => 'url',
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_REJECTED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->chargeService->method('chargeNewCreditCard')->with($transaction)->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $transactionAfterLookup = $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );

        $this->assertFalse($transactionAfterLookup->with3D());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_write_the_bi_event_once_when_3ds_performed(): void
    {
        $this->biService->expects($this->once())->method('write');

        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reasonCode'   => '202',
                            'responseCode' => '2',
                            'PAREQ'         => 'SimulatedPAREQ1000177E01C7F93',
                            'acsURL'        => $this->faker->url,
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_write_the_bi_event_when_3ds_not_performed(): void
    {
        $this->biService->expects($this->once())->method('write');

        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success',
                            'PAREQ'         => 'pareq',
                            'acsURL'        => 'url',
                        ],
                        'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_REJECTED,
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->chargeService->method('chargeNewCreditCard')->with($transaction)->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );
    }

    /**
     * @test
     *
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws JsonException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_write_two_bi_event_when_3ds_is_performed_with_frictionless(): void
    {
        $this->biService->expects($this->exactly(2))->method('write');

        $command = $this->createPerformRocketgateSaleCommandSingleCharge(['useThreeD' => true]);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $this->lookupTranslatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => [
                            'request'     => 'json',
                            'use3DSecure' => true
                        ],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success',
                            'PAREQ'         => 'pareq',
                            'acsURL'        => 'url',
                        ],
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->chargeService->method('chargeNewCreditCard')->with($transaction)->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => '0',
                            'responseCode' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => 'Success',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $this->service->performTransaction(
            $transaction,
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_MONTH_1'],
            $_ENV['ROCKETGATE_CARD_EXPIRE_YEAR_1'],
            '123',
            'deviceId',
            'url',
            '2',
            false
        );
    }
}
