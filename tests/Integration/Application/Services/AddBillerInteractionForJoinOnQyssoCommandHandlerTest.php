<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Service;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnQyssoCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnQyssoCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\SiteId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\QyssoService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;
use Tests\IntegrationTestCase;

class AddBillerInteractionForJoinOnQyssoCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|QyssoService */
    protected $epochService;

    /** @var MockObject|QyssoJoinPostbackHttpCommandDTOAssembler */
    protected $dto;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var MockObject|AddBillerInteractionForJoinOnQyssoCommand */
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

        $this->repository   = $this->createMock(TransactionRepository::class);
        $this->dto          = $this->createMock(QyssoJoinPostbackHttpCommandDTOAssembler::class);
        $this->epochService = $this->createMock(QyssoService::class);
        $this->bi           = $this->createMock(BILoggerService::class);

        $this->validCommand = $this->createMock(AddBillerInteractionForJoinOnQyssoCommand::class);
        $this->validCommand->method('transactionId')->willReturn($this->transactionId);
        $this->validCommand->method('payload')->willReturn('{\"reply_code\":\"002\",\"reply_desc\":\"No details\",\"trans_id\":\"26\",\"trans_date\":\"12/28/2020 5:29:23 PM\",\"trans_amount\":\"55.3\",\"trans_currency\":\"1\",\"trans_order\":\"e6343bc3-5104-4c51-80ac-6c0e7df9d2f4\",\"merchant_id\":\"9440387\",\"client_fullname\":\"O`brean P_monto\",\"client_phone\":\"5140000911\",\"client_email\":\"qysso.pgtest012@test.mindgeek.com\",\"payment_details\":\"Visa .... 0000\",\"signature\":\"hJrOF2EZhG5cvjyBreW27c1CKoDxARgBPaUNl1YZCl8=\"}');
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): void
    {
        $transaction = $this->mockedTransaction();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertInstanceOf(QyssoJoinPostbackCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_write_the_bi_event(): void
    {
        $transaction = $this->mockedTransaction();

        $this->bi->expects($this->once())->method('write');

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_invalid_transaction_information_exception_when_id_is_not_uuid(): void
    {
        $this->expectException(InvalidTransactionInformationException::class);

        /** @var AddBillerInteractionForJoinOnQyssoCommand|MockObject $command */
        $command = $this->createMock(AddBillerInteractionForJoinOnQyssoCommand::class);
        $command->method('transactionId')->willReturn('asdasd');
        $command->method('payload')->willReturn(
            '{\"reply_code\":\"002\",\"reply_desc\":\"No details\",\"trans_id\":\"26\",\"trans_date\":\"12/28/2020 5:29:23 PM\",\"trans_amount\":\"55.3\",\"trans_currency\":\"1\",\"trans_order\":\"e6343bc3-5104-4c51-80ac-6c0e7df9d2f4\",\"merchant_id\":\"9440387\",\"client_fullname\":\"O`brean P_monto\",\"client_phone\":\"5140000911\",\"client_email\":\"qysso.pgtest012@test.mindgeek.com\",\"payment_details\":\"Visa .... 0000\",\"signature\":\"hJrOF2EZhG5cvjyBreW27c1CKoDxARgBPaUNl1YZCl8=\"}'
        );

        $this->repository->method('findById')->willReturn(null);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_exception_when_transaction_not_found(): void
    {
        $this->expectException(TransactionNotFoundException::class);

        $this->repository->method('findById')->willReturn(null);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_merchant_exception_when_merchant_id_is_not_qysso(): void
    {
        $this->expectException(InvalidBillerException::class);

        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_same_transaction_state_when_trying_to_update_from_the_same_state(): void
    {
        $this->expectException(PostbackAlreadyProcessedException::class);

        $epochBillerResponse = $this->createMock(QyssoPostbackBillerResponse::class);
        $epochBillerResponse->method('code')->willReturn(
            (string) QyssoPostbackBillerResponse::CHARGE_RESULT_APPROVED
        );
        $epochBillerResponse->method('approved')->willReturn(true);
        $epochBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr'], JSON_THROW_ON_ERROR));
        $epochBillerResponse->method('requestDate')->willReturn(new DateTimeImmutable());

        $transaction = $this->mockedTransaction(false);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            $this->dto,
            $this->epochService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_status_declined_response(): void
    {
        $transaction = $this->mockedTransaction(true, Declined::NAME);


        $epochBillerResponse = $this->createMock(QyssoPostbackBillerResponse::class);
        $epochBillerResponse->method('code')->willReturn(
            (string) QyssoPostbackBillerResponse::CHARGE_RESULT_DECLINED
        );
        $epochBillerResponse->method('declined')->willReturn(true);
        $epochBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $epochBillerResponse->method('requestDate')->willReturn(new DateTimeImmutable());

        $this->epochService->method('translatePostback')->willReturn($epochBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            new QyssoJoinPostbackHttpCommandDTOAssembler(),
            $this->epochService,
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
    public function execute_with_invalid_type_id_value_should_return_status_aborted_response(): void
    {
        $transaction = $this->mockedTransaction(true, Aborted::NAME);

        $this->validCommand = $this->createMock(AddBillerInteractionForJoinOnQyssoCommand::class);
        $this->validCommand->method('transactionId')->willReturn($this->transactionId);
        $this->validCommand->method('payload')->willReturn(
            '{\"reply_code\":\"002\",\"reply_desc\":\"No details\",\"trans_id\":\"26\",\"trans_date\":\"12/28/2020 5:29:23 PM\",\"trans_amount\":\"55.3\",\"trans_currency\":\"1\",\"trans_order\":\"e6343bc3-5104-4c51-80ac-6c0e7df9d2f4\",\"merchant_id\":\"9440387\",\"client_fullname\":\"O`brean P_monto\",\"client_phone\":\"5140000911\",\"client_email\":\"qysso.pgtest012@test.mindgeek.com\",\"payment_details\":\"Visa .... 0000\",\"signature\":\"hJrOF2EZhG5cvjyBreW27c1CKoDxARgBPaUNl1YZCl8=\"}'
        );

        $qyssoBillerResponse = $this->createMock(QyssoPostbackBillerResponse::class);
        $qyssoBillerResponse->method('aborted')->willReturn(true);
        $qyssoBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $qyssoBillerResponse->method('requestDate')->willReturn(new DateTimeImmutable());

        $this->epochService->method('translatePostback')->willReturn($qyssoBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnQyssoCommandHandler(
            $this->repository,
            new QyssoJoinPostbackHttpCommandDTOAssembler(),
            $this->epochService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Aborted::NAME, $result->jsonSerialize()['status']);
    }


    /**
     * @param bool   $isPendingFlag  Pending flag
     * @param string $responseStatus Status
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
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

        $epochBillerChargeSettings = $this->createMock(QyssoBillerSettings::class);

        $billerInteractions = new BillerInteractionCollection();
        $billerInteractions->add($this->createQyssoRequestBillerInteraction());
        $billerInteractions->add($this->createQyssoResponseBillerInteraction());

        $chargeInformation = $this->createChargeInformationSingleCharge();

        $transaction = $this->createMock(ChargeTransaction::class);
        $transaction->method('billerName')->willReturn(QyssoBillerSettings::QYSSO);
        $transaction->method('status')->willReturn($status);
        $transaction->method('billerChargeSettings')->willReturn($epochBillerChargeSettings);
        $transaction->method('billerInteractions')->willReturn($billerInteractions);
        $transaction->method('chargeInformation')->willReturn($chargeInformation);
        $transaction->method('paymentType')->willReturn('banktransfer');
        $transaction->method('siteId')->willReturn(SiteId::createFromString($this->faker->uuid));

        return $transaction;
    }
}
