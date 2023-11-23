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
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;

class PerformRocketgateStartRebillCommandHandler extends BaseCommandHandler
{
    use CreateUpdateRebillTransactionTrait;

    /**
     * @var UpdateRebillService
     */
    private $updateRebillService;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;

    /**
     * @var DeclinedBillerResponseExtraDataRepository
     */
    protected $declinedBillerResponseExtraDataRepository;

    /**
     * PerformRocketgateSaleCommandHandler constructor.
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              The dto assembler
     * @param TransactionRepository                     $repository                                The repository object
     * @param UpdateRebillService                       $updateRebillService                       The update rebill service
     * @param BILoggerService                           $biLoggerService                           The BiLogger
     * @param DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository Repository
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        UpdateRebillService $updateRebillService,
        BILoggerService $biLoggerService,
        DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->updateRebillService                       = $updateRebillService;
        $this->biLoggerService                           = $biLoggerService;
        $this->declinedBillerResponseExtraDataRepository = $declinedBillerResponseExtraDataRepository;
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
            if (!$command instanceof PerformRocketgateUpdateRebillCommand) {
                throw new InvalidCommandException(PerformRocketgateUpdateRebillCommand::class, $command);
            }

            Log::info('Begin processing start rebill transaction');

            if (empty($command->transactionId())) {
                throw new MissingTransactionInformationException('transactionId');
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->transactionId());
            }

            $this->validateBillerFiels($command, $previousTransaction);

            // Create entity
            $transaction = $this->createRocketgateUpdateRebillTransaction($command, $previousTransaction);

            // Perform suspend
            /** @var RocketgateBillerResponse $billerResponse */
            $billerResponse = $this->updateRebillService->start($transaction);

            // Update transaction
            $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new RebillUpdateTransactionCreated(
                $transaction,
                $billerResponse,
                RocketGateBillerSettings::ROCKETGATE,
                RocketGateBillerSettings::ACTION_START
            );
            $this->biLoggerService->write($event);

            // Add error classification based on the biller response.
            if ($billerResponse->declined() || $billerResponse->aborted()) {
                // Build mapping criteria based on biller response.
                $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

                // Get extra data to build the error classification.
                $extraData = $this->declinedBillerResponseExtraDataRepository->retrieve($mappingCriteria);

                $errorClassification = new ErrorClassification($mappingCriteria, $extraData);

                Log::info('ErrorClassification', $errorClassification->toArray());
            }

            /** TODO done to align with the new TS */
            $rgBillerInteractions = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
                $transaction->billerInteractions(),
                $transaction->threedsVersion() > 0 ? true : false
            );

            $transaction->billerTransactions = $rgBillerInteractions->getEncodedBillerTransactions();
            if ($billerResponse->approved() || $billerResponse->pending()) {
                $transaction->subsequentOperationFields = $rgBillerInteractions->getEncodedSubsequentOperationFields();
            }

            // Persist updated transaction entity
            $this->repository->update($transaction);

            // Return DTO
            return $this->dtoAssembler->assemble($transaction, ($errorClassification ?? null));
        } catch (InvalidPayloadException | TransactionNotFoundException | \InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
