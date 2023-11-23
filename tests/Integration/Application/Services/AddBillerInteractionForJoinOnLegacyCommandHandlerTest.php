<?php
declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use Faker\Guesser\Name;
use Money\Currency;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\AddBillerInteractionForJoinOnLegacyCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\AddBillerInteractionForJoinOnLegacyCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Chargedback;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingSiteIdForCrossSaleException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Refunded;
use ProBillerNG\Transaction\Domain\Model\SiteId;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\LegacyPostbackResponseService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;
use Tests\IntegrationTestCase;

/**
 * @group legacyService
 * Class AddBillerInteractionForJoinOnLegacyCommandHandlerTest
 * @package Tests\Integration\Application\Services
 */
class AddBillerInteractionForJoinOnLegacyCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|LegacyPostbackResponseService */
    protected $legacyPostbackResponseService;

    /** @var MockObject|LegacyJoinPostbackHttpCommandDTOAssembler */
    protected $dto;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var MockObject|AddBillerInteractionForJoinOnLegacyCommand */
    protected $validCommand;

    /** @var string */
    protected $transactionId;

    /**
     * Setup test
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->transactionId = $this->faker->uuid;

        $this->legacyPostbackResponseService = $this->createMock(LegacyPostbackResponseService::class);

        $this->repository = $this->createMock(TransactionRepository::class);
        $this->dto        = $this->createMock(LegacyJoinPostbackHttpCommandDTOAssembler::class);
        $this->bi         = $this->createMock(BILoggerService::class);

        $this->validCommand = $this->createMock(AddBillerInteractionForJoinOnLegacyCommand::class);
        $this->validCommand->method('transactionId')->willReturn($this->transactionId);
        $this->validCommand->method('payload')->willReturn(
            [
                'transaction_id'  => '1201415378',
                'payment_type'    => 'CC',
                'payment_subtype' => 'VS',
                'ans'             => 'Y412081UU |2344619085',
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_should_return_a_dto_when_a_valid_command_is_provided(): void
    {
        $transaction = $this->mockedTransaction();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            $this->dto,
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertInstanceOf(LegacyJoinPostbackCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_should_write_the_bi_event(): void
    {
        $transaction = $this->mockedTransaction();
        $transaction->method('isNotProcessed')->willReturn(true);

        $this->bi->expects($this->once())->method('write');

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            $this->dto,
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }


    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_should_throw_exception_when_transaction_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->repository->method('findById')->willReturn(null);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            $this->dto,
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @param string $responseStatus Response Status
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingSiteIdForCrossSaleException
     * @throws TransactionNotFoundException
     * @throws InvalidCommandException
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidTransactionInformationException
     * @dataProvider returnNotPendingStatus
     */
    public function it_should_not_update_nor_send_events_if_transaction_is_not_pending_and_request_response_type_is_return(string $responseStatus): void
    {
        $transaction = $this->mockedTransaction(false, $responseStatus);

        $this->bi->expects($this->never())->method('write');
        $this->repository->expects($this->never())->method('update');
        $transaction->expects($this->never())->method('updateLegacyTransactionFromBillerResponse');

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            $this->dto,
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $this->validCommand->method('type')
            ->willReturn('return');

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @param string $responseStatus Response Status
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingSiteIdForCrossSaleException
     * @throws TransactionNotFoundException
     * @throws InvalidCommandException
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidTransactionInformationException
     * @dataProvider returnNotPendingStatus
     */
    public function it_should_not_update_nor_send_events_if_transaction_is_not_pending_and_request_response_type_is_postback(string $responseStatus): void
    {
        $transaction = $this->mockedTransaction(false, $responseStatus);

        $this->bi->expects($this->never())->method('write');
        $this->repository->expects($this->never())->method('update');
        $transaction->expects($this->never())->method('updateLegacyTransactionFromBillerResponse');

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            new LegacyJoinPostbackHttpCommandDTOAssembler(),
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $this->validCommand->method('type')
            ->willReturn('postback');

        $result = $handler->execute($this->validCommand);
        $this->assertSame($responseStatus, $result->jsonSerialize()['status']);
    }

    /**
     * @return array
     */
    public function returnNotPendingStatus(): array
    {
        return [
            ['approved'     => Approved::NAME],
            ['declined'     => Declined::NAME],
            ['aborted'      => Aborted::NAME],
            ['refunded'     => Refunded::NAME],
            ['charged_back' => Chargedback::NAME]
        ];
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_should_return_status_declined_response(): void
    {
        $transaction = $this->mockedTransaction(true, Declined::NAME);

        $legacyPostbackBillerResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $legacyPostbackBillerResponse->method('code')->willReturn(
            (string) LegacyPostbackBillerResponse::CHARGE_RESULT_DECLINED
        );
        $legacyPostbackBillerResponse->method('declined')->willReturn(true);
        $legacyPostbackBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $legacyPostbackBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $this->legacyPostbackResponseService->method('translate')->willReturn($legacyPostbackBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            new LegacyJoinPostbackHttpCommandDTOAssembler(),
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Declined::NAME, $result->jsonSerialize()['status']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_should_return_status_approved_response(): void
    {
        $transaction = $this->mockedTransaction(true, Approved::NAME);


        $legacyPostbackBillerResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $legacyPostbackBillerResponse->method('code')->willReturn(
            (string) LegacyPostbackBillerResponse::CHARGE_RESULT_APPROVED
        );
        $legacyPostbackBillerResponse->method('declined')->willReturn(true);
        $legacyPostbackBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $legacyPostbackBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $this->legacyPostbackResponseService->method('translate')->willReturn($legacyPostbackBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            new LegacyJoinPostbackHttpCommandDTOAssembler(),
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Approved::NAME, $result->jsonSerialize()['status']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_transaction_when_main_transaction_is_not_pending_and_it_is_a_cross_sale(): void
    {
        $mainTransaction = $this->mockedTransaction(false, Approved::NAME);

        $legacyPostbackBillerResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $legacyPostbackBillerResponse->method('code')->willReturn(
            (string) LegacyPostbackBillerResponse::CHARGE_RESULT_APPROVED
        );
        $legacyPostbackBillerResponse->method('isCrossSale')->willReturn(
            true
        );

        $legacyPostbackBillerResponse->method('amount')->willReturn(
            Amount::create(10.0)
        );

        $legacyPostbackBillerResponse->method('approved')->willReturn(true);
        $legacyPostbackBillerResponse->method('declined')->willReturn(false);
        $legacyPostbackBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $legacyPostbackBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $this->legacyPostbackResponseService->method('translate')->willReturn($legacyPostbackBillerResponse);

        $this->repository->method('findById')->willReturn($mainTransaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            new LegacyJoinPostbackHttpCommandDTOAssembler(),
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $this->validCommand->method('siteId')->willReturn($this->faker->uuid);

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Approved::NAME, $result->jsonSerialize()['status']);
        $this->assertNotEmpty($result->jsonSerialize()['transactionId']);
        $this->assertNotEquals((string) $mainTransaction->transactionId(), $result->jsonSerialize()['transactionId']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_exception_if_it_is_to_create_cross_sale_transaction_and_there_is_no_site_id(): void
    {
        $this->expectException(MissingSiteIdForCrossSaleException::class);

        $legacyPostbackBillerResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $legacyPostbackBillerResponse->method('isCrossSale')->willReturn(
            true
        );

        $this->legacyPostbackResponseService->method('translate')->willReturn($legacyPostbackBillerResponse);

        $transaction = $this->mockedTransaction(false);
        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnLegacyCommandHandler(
            new LegacyJoinPostbackHttpCommandDTOAssembler(),
            $this->repository,
            $this->legacyPostbackResponseService,
            $this->bi
        );

        $this->validCommand->method('type')
            ->willReturn('postback');
        $this->validCommand->method('siteId')
            ->willReturn(null);

        $handler->execute($this->validCommand);
    }

    /**
     * @param bool   $isPendingFlag  Pending flag
     * @param string $responseStatus Status
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    private function mockedTransaction(
        $isPendingFlag = true,
        string $responseStatus = Approved::NAME
    ): ChargeTransaction {
        $status = $this->createMock(Pending::class);
        $status->method('pending')->willReturn($isPendingFlag);
        $status->method('__toString')->willReturn($responseStatus);

        $legacyBillerChargeSettings = $this->createMock(LegacyBillerChargeSettings::class);

        $mockedChargeInformation = $this->createMock(ChargeInformation::class);
        $mockedChargeInformation->method('currency')->willReturn(\ProBillerNG\Transaction\Domain\Model\Currency::create('USD'));
        $mockedChargeInformation->method('amount')->willReturn(Amount::create(10.0));


        $transaction = $this->createMock(ChargeTransaction::class);
        $transaction->method('transactionId')->willReturn(TransactionId::create());
        $transaction->method('siteId')->willReturn(SiteId::create());
        $transaction->method('billerName')->willReturn(LegacyBillerChargeSettings::LEGACY);
        $transaction->method('status')->willReturn($status);
        $transaction->method('billerChargeSettings')->willReturn($legacyBillerChargeSettings);
        $transaction->method('chargeInformation')->willReturn($mockedChargeInformation);
        $transaction->method('siteId')->willReturn(SiteId::createFromString($this->faker->uuid));

        return $transaction;
    }
}
