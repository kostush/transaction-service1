<?php

declare(strict_types=1);

namespace Tests\Integration\Application\Service;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use Tests\IntegrationTestCase;

class AddBillerInteractionForJoinOnPumapayCommandHandlerTest extends IntegrationTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /** @var MockObject|PumapayService */
    protected $pumapayService;

    /** @var MockObject|PumapayJoinPostbackHttpCommandDTOAssembler */
    protected $dto;

    /** @var MockObject|BILoggerService */
    protected $bi;

    /** @var MockObject|AddBillerInteractionForJoinOnPumapayCommand */
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

        $this->repository     = $this->createMock(TransactionRepository::class);
        $this->dto            = $this->createMock(PumapayJoinPostbackHttpCommandDTOAssembler::class);
        $this->pumapayService = $this->createMock(PumapayService::class);
        $this->bi             = $this->createMock(BILoggerService::class);

        $this->validCommand = $this->createMock(AddBillerInteractionForJoinOnPumapayCommand::class);
        $this->validCommand->method('transactionId')->willReturn($this->transactionId);
        $this->validCommand->method('payload')->willReturn(
            [
                'transactionData' => [
                    'statusID' => 3,
                    'typeID'   => 5,
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertInstanceOf(PumapayJoinPostbackCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    public function it_should_write_the_bi_event(): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $this->bi->expects($this->once())->method('write');

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
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

        /** @var AddBillerInteractionForJoinOnPumapayCommand|MockObject $command */
        $command = $this->createMock(AddBillerInteractionForJoinOnPumapayCommand::class);
        $command->method('transactionId')->willReturn('asdasd');
        $command->method('payload')->willReturn(
            [
                'transactionData' => [
                    'statusID' => 3,
                    'typeID'   => 5,
                ],
            ]
        );

        $this->repository->method('findById')->willReturn(null);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
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

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_merchant_exception_when_merchant_id_is_not_pumapay(): void
    {
        $this->expectException(InvalidBillerNameException::class);

        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    public function it_should_throw_same_transaction_state_when_trying_to_update_from_the_same_state(): void
    {
        $this->expectException(PostbackAlreadyProcessedException::class);

        $pumapayBillerResponse = $this->createMock(PumapayPostbackBillerResponse::class);
        $pumapayBillerResponse->method('code')->willReturn(
            (string) PumapayPostbackBillerResponse::CHARGE_RESULT_APPROVED
        );
        $pumapayBillerResponse->method('approved')->willReturn(true);
        $pumapayBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr'], JSON_THROW_ON_ERROR));
        $pumapayBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();
        $transaction->updatePumapayTransactionFromBillerResponse($pumapayBillerResponse);
        $transaction->approve();

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            $this->dto,
            $this->pumapayService,
            $this->bi
        );

        $handler->execute($this->validCommand);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    public function it_should_return_status_declined_response(): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $pumapayBillerResponse = $this->createMock(PumapayPostbackBillerResponse::class);
        $pumapayBillerResponse->method('code')->willReturn(
            (string) PumapayPostbackBillerResponse::CHARGE_RESULT_DECLINED
        );
        $pumapayBillerResponse->method('declined')->willReturn(true);
        $pumapayBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $pumapayBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $this->pumapayService->method('translatePostback')->willReturn($pumapayBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            new PumapayJoinPostbackHttpCommandDTOAssembler(),
            $this->pumapayService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Declined::NAME, $result->jsonSerialize()['status']);
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    public function execute_with_invalid_type_id_value_should_return_status_aborted_response(): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $this->validCommand = $this->createMock(AddBillerInteractionForJoinOnPumapayCommand::class);
        $this->validCommand->method('transactionId')->willReturn($this->transactionId);
        $this->validCommand->method('payload')->willReturn(
            [
                'transactionData' => [
                    'statusID' => 3,
                    'typeID'   => 8,
                ],
            ]
        );

        $pumapayBillerResponse = $this->createMock(PumapayPostbackBillerResponse::class);
        $pumapayBillerResponse->method('aborted')->willReturn(true);
        $pumapayBillerResponse->method('requestPayload')->willReturn(json_encode(['someStr']));
        $pumapayBillerResponse->method('requestDate')->willReturn(new \DateTimeImmutable());

        $this->pumapayService->method('translatePostback')->willReturn($pumapayBillerResponse);

        $this->repository->method('findById')->willReturn($transaction);

        $handler = new AddBillerInteractionForJoinOnPumapayCommandHandler(
            $this->repository,
            new PumapayJoinPostbackHttpCommandDTOAssembler(),
            $this->pumapayService,
            $this->bi
        );

        $result = $handler->execute($this->validCommand);

        $this->assertSame(Aborted::NAME, $result->jsonSerialize()['status']);
    }
}
