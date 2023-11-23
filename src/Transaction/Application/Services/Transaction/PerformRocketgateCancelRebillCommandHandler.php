<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\CreateUpdateRebillTransactionTrait;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;

class PerformRocketgateCancelRebillCommandHandler extends BaseCommandHandler
{
    use CreateUpdateRebillTransactionTrait;

    /**
     * @var ChargeService
     */
    private $chargeService;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;

    /**
     * PerformRocketgateSaleCommandHandler constructor.
     * @param HttpCommandDTOAssembler $dtoAssembler    The dto assembler
     * @param TransactionRepository   $repository      The repository object
     * @param ChargeService           $chargeService   The charge service
     * @param BILoggerService         $biLoggerService The BiLogger
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        ChargeService $chargeService,
        BILoggerService $biLoggerService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService   = $chargeService;
        $this->biLoggerService = $biLoggerService;
    }

    /**
     * Execute the create transaction command
     *
     * @param Command $command The create transaction command
     *
     * @return TransactionCommandHttpDTO
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws TransactionNotFoundException
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformRocketgateCancelRebillCommand) {
                throw new InvalidCommandException(PerformRocketgateCancelRebillCommand::class, $command);
            }

            Log::info('Begin processing cancel rebill transaction');

            if (empty($command->transactionId())) {
                throw new MissingTransactionInformationException('transactionId');
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );
            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->transactionId());
            }

            // Create entity
            $transaction = $this->createRocketgateCancelRebillTransaction($command, $previousTransaction);

            // Perform suspend
            $billerResponse = $this->chargeService->suspendRebill($transaction);

            // Update transaction
            $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new RebillUpdateTransactionCreated(
                $transaction,
                $billerResponse,
                RocketgateBillerSettings::ROCKETGATE,
                RocketgateBillerSettings::ACTION_CANCEL
            );
            $this->biLoggerService->write($event);

            // Return DTO
            return $this->dtoAssembler->assemble($transaction);
        } catch (InvalidPayloadException | TransactionNotFoundException | \InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
