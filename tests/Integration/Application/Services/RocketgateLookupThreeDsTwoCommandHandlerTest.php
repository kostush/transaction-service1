<?php
declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionLookupException;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateLookupThreeDsTwoCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPreviousTransactionStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateLookupThreeDsTwoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\DoctrineDeclinedBillerResponseExtraDataRocketgateRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\IntegrationTestCase;

class RocketgateLookupThreeDsTwoCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var RocketgateLookupThreeDsTwoCommandHandler
     */
    private $handler;

    /**
     * @var LookupThreeDsTwoService
     */
    private $lookupService;

    /**
     * @var FirestoreTransactionRepository|MockObject
     */
    private $repositoryMock;

    /**
     * @var MockObject|RedisRepository
     */
    private $redisRepoMock;

    /**
     * @var MockObject|DeclinedBillerResponseExtraDataRepository
     */
    private $declinedBillerResponseRepository;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->lookupService = $this->retrieveLookupService(
            [
                'request'  => [
                    'request'     => 'json',
                    'use3DSecure' => true
                ],
                'response' => [
                    'reasonCode'            => '0',
                    'responseCode'          => '0',
                    'reasonDesc'            => 'Success',
                    '_3DSECURE_STEP_UP_URL' => 'url',
                    '_3DSECURE_STEP_UP_JWT' => 'jwt',
                    'guidNo'                => 'billerTransactionId',
                ],
                'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                'code'     => '2',
            ]
        );

        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $this->repositoryMock = $this->createMock(FirestoreTransactionRepository::class);
        $this->redisRepoMock  = $this->createMock(RedisRepository::class);

        /** @var DeclinedBillerResponseExtraDataRepository $declinedBillerResponseRepository */
        $this->declinedBillerResponseRepository = $this->createMock(
            DeclinedBillerResponseExtraDataRepository::class
        );

        parent::setUp();

        $this->handler = new RocketgateLookupThreeDsTwoCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->repositoryMock,
            $this->lookupService,
            $this->redisRepoMock,
            $this->declinedBillerResponseRepository
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_an_exception_when_invalid_command_given(): void
    {
        $this->expectException(TransactionLookupException::class);

        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionLookupException
     * @throws MissingCreditCardInformationException
     * @throws TransactionNotFoundException
     * @throws TransactionLookupException
     * @throws TransactionNotFoundException
     */
    public function it_should_throw_an_exception_when_transaction_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $command = $this->createLookupThreeDsTwoCommand();
        $this->repositoryMock->method('findById')->willReturn(null);

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_performing_a_threeds_two_operation(): array
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

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'payload' => json_encode(
                        [
                            'reasonCode'            => '0',
                            'responseCode'          => '0',
                            'reasonDesc'            => 'Success',
                            '_3DSECURE_STEP_UP_URL' => 'url',
                            '_3DSECURE_STEP_UP_JWT' => 'jwt',
                            'guidNo'                => 'billerTransactionId',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'type'    => 'response',
                    'payload' => json_encode(
                        [
                            'reasonCode'            => '225',
                            'responseCode'          => '2',
                            '_3DSECURE_STEP_UP_URL' => 'url',
                            '_3DSECURE_STEP_UP_JWT' => 'jwt',
                            'guidNo'                => 'billerTransactionId',
                            'merchantAccount'       => '2',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $this->repositoryMock->method('findById')->willReturn($transaction);
        $command = $this->createLookupThreeDsTwoCommand();

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_transaction_id_when_performing_a_threeds_two_operation(array $result): void
    {
        $this->assertArrayHasKey('transactionId', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_status_when_performing_a_threeds_two_operation(array $result): void
    {
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_threed_node_when_performing_a_threeds_two_operation(array $result): void
    {
        $this->assertArrayHasKey('threeD', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_version_on_the_threed_node_when_performing_a_threeds_two_operation(
        array $result
    ): void {
        $this->assertArrayHasKey('version', $result['threeD']);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_step_up_url_on_the_threed_node_when_performing_a_threeds_two_operation(
        array $result
    ): void {
        $this->assertArrayHasKey('stepUpUrl', $result['threeD']);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_step_up_jwt_on_the_threed_node_when_performing_a_threeds_two_operation(
        array $result
    ): void {
        $this->assertArrayHasKey('stepUpJwt', $result['threeD']);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_two_operation
     * @return void
     */
    public function it_should_have_a_md_on_the_threed_node_when_performing_a_threeds_two_operation(array $result): void
    {
        $this->assertArrayHasKey('md', $result['threeD']);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_performing_a_threeds_one_operation(): array
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

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'payload' => json_encode(
                        [
                            'reasonCode'   => '0',
                            'responseCode' => '0',
                            'reasonDesc'   => 'Success',
                            'PAREQ'        => 'pareq',
                            'acsURL'       => 'acs',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'type'    => 'response',
                    'payload' => json_encode(
                        [
                            'reasonCode'      => '0',
                            'responseCode'    => '0',
                            'reasonDesc'      => 'Success',
                            'PAREQ'           => 'pareq',
                            'acsURL'          => 'acs',
                            'merchantAccount' => '2',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $this->repositoryMock->method('findById')->willReturn($transaction);

        $this->lookupService = $this->retrieveLookupService(
            [
                'request'  => [
                    'request'     => 'json',
                    'use3DSecure' => true
                ],
                'response' => [
                    'reasonCode'   => '0',
                    'responseCode' => '0',
                    'reasonDesc'   => 'Success',
                    'PAREQ'        => 'pareq',
                    'acsURL'       => 'url',
                ],
                'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                'code'     => '2',
            ]
        );

        $command = $this->createLookupThreeDsTwoCommand();

        $this->handler = new RocketgateLookupThreeDsTwoCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->repositoryMock,
            $this->lookupService,
            $this->redisRepoMock,
            $this->declinedBillerResponseRepository
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_transaction_id_when_performing_a_threeds_one_operation(array $result): void
    {
        $this->assertArrayHasKey('transactionId', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_status_when_performing_a_threeds_one_operation(array $result): void
    {
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_threed_node_when_performing_a_threeds_one_operation(array $result): void
    {
        $this->assertArrayHasKey('threeD', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_version_on_the_threed_node_when_performing_a_threeds_one_operation(array $result
    ): void {
        $this->assertArrayHasKey('version', $result['threeD']);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_pareq_on_the_threed_node_when_performing_a_threeds_one_operation(array $result
    ): void {
        $this->assertArrayHasKey('pareq', $result['threeD']);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_performing_a_threeds_one_operation
     * @return void
     */
    public function it_should_have_a_acs_on_the_threed_node_when_performing_a_threeds_one_operation(array $result): void
    {
        $this->assertArrayHasKey('acs', $result['threeD']);
    }

    /**
     * @test
     * @return array
     * @throws TransactionLookupException
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionNotFoundException
     */
    public function it_should_return_a_dto_when_trying_to_perform_a_threeds_transaction_but_it_switches_to_a_non_threeds_transaction(): array
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

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'payload' => json_encode(
                        [
                            'reasonCode'   => '0',
                            'responseCode' => '0',
                            'reasonDesc'   => 'Success',
                            'PAREQ'        => 'pareq',
                            'acsURL'       => 'acs',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'type'    => 'response',
                    'payload' => json_encode(
                        [
                            'reasonCode'      => '0',
                            'responseCode'    => '0',
                            'reasonDesc'      => 'Success',
                            'PAREQ'           => 'pareq',
                            'acsURL'          => 'acs',
                            'merchantAccount' => '2',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $this->lookupService = $this->retrieveLookupService(
            [
                'request'  => [
                    'request'     => 'json',
                    'use3DSecure' => true
                ],
                'response' => [
                    'reasonCode'            => '0',
                    'responseCode'          => '0',
                    'reasonDesc'            => 'Success',
                    '_3DSECURE_STEP_UP_URL' => 'url',
                    '_3DSECURE_STEP_UP_JWT' => 'jwt',
                    'guidNo'                => 'billerTransactionId',
                ],
                'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_REJECTED,
                'code'     => '2',
            ]
        );

        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $this->repositoryMock = $this->createMock(FirestoreTransactionRepository::class);

        $this->repositoryMock->method('findById')->willReturn($transaction);

        $command = $this->createLookupThreeDsTwoCommand();

        $this->handler = new RocketgateLookupThreeDsTwoCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->repositoryMock,
            $this->lookupService,
            $this->redisRepoMock,
            $this->declinedBillerResponseRepository
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_trying_to_perform_a_threeds_transaction_but_it_switches_to_a_non_threeds_transaction
     * @return void
     */
    public function it_should_have_a_transaction_id_when_performing_a_non_threeds_operation(array $result): void
    {
        $this->assertArrayHasKey('transactionId', $result);
    }

    /**
     * @test
     *
     * @param array $result Result
     *
     * @depends it_should_return_a_dto_when_trying_to_perform_a_threeds_transaction_but_it_switches_to_a_non_threeds_transaction
     * @return void
     */
    public function it_should_have_a_status_when_performing_a_non_threeds_operation(array $result): void
    {
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * @param array $billerInteraction Biller interaction
     *
     * @return LookupThreeDsTwoService
     * @throws Exception
     */
    private function retrieveLookupService(array $billerInteraction): LookupThreeDsTwoService
    {
        $translatingService = $this->createMock(LookupThreeDsTwoTranslatingService::class);
        $translatingService->method('performLookup')->willReturn(
            RocketgateLookupThreeDsTwoBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    $billerInteraction,
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        $chargeService = $this->createMock(ChargeService::class);
        $chargeService->method('chargeNewCreditCard')->willReturn(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => '0',
                            'responseCode' => '0',
                            'reasonDesc'   => 'Success'
                        ],
                        'reason'   => '111',
                        'code'     => '1',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );

        return new LookupThreeDsTwoService(
            $translatingService,
            $chargeService,
            $this->createMock(BILoggerService::class)
        );
    }

    /**
     * @test
     * @return mixed
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionLookupException
     * @throws TransactionNotFoundException
     */
    public function it_should_store_cvv_when_performing_a_threeds_operation()
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

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'payload' => json_encode(
                        [
                            'reasonCode'   => '0',
                            'responseCode' => '0',
                            'reasonDesc'   => 'Success',
                            'PAREQ'        => 'pareq',
                            'acsURL'       => 'acs',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'type'    => 'response',
                    'payload' => json_encode(
                        [
                            'reasonCode'      => '0',
                            'responseCode'    => '0',
                            'reasonDesc'      => 'Success',
                            'PAREQ'           => 'pareq',
                            'acsURL'          => 'acs',
                            'merchantAccount' => '2',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                ]
            )
        );

        $this->repositoryMock->method('findById')->willReturn($transaction);

        $this->lookupService = $this->retrieveLookupService(
            [
                'request'  => [
                    'request'     => 'json',
                    'use3DSecure' => true
                ],
                'response' => [
                    'reasonCode'   => '0',
                    'responseCode' => '0',
                    'reasonDesc'   => 'Success',
                    'PAREQ'        => 'pareq',
                    'acsURL'       => 'url',
                ],
                'reason'   => (string) RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                'code'     => '2',
            ]
        );

        $command = $this->createLookupThreeDsTwoCommand();

        $this->redisRepoMock->expects($this->once())->method('storeCvv');

        $this->handler = new RocketgateLookupThreeDsTwoCommandHandler(
            new HttpCommandDTOAssembler(),
            $this->repositoryMock,
            $this->lookupService,
            $this->redisRepoMock,
            $this->declinedBillerResponseRepository
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionLookupException
     * @throws MissingCreditCardInformationException
     * @throws TransactionNotFoundException
     * @throws TransactionLookupException
     * @throws TransactionNotFoundException
     */
    public function it_should_throw_an_exception_when_previous_transaction_is_not_pending(): void
    {
        $this->expectException(InvalidPreviousTransactionStatusException::class);

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

        $transaction->setStatus(Aborted::NAME);

        $command = $this->createLookupThreeDsTwoCommand();
        $this->repositoryMock->method('findById')->willReturn($transaction);

        $this->handler->execute($command);
    }
}
