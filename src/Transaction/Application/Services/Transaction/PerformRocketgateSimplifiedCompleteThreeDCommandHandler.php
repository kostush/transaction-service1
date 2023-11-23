<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use Exception;
use InvalidArgumentException;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use Throwable;

class PerformRocketgateSimplifiedCompleteThreeDCommandHandler extends BaseCommandHandler
{
    /**
     * @var ChargeService
     */
    private $chargeService;

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
     * @param ChargeService                             $chargeService                             The charge service
     * @param BILoggerService                           $biLoggerService                           The BiLogger
     * @param DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository Repository
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        ChargeService $chargeService,
        BILoggerService $biLoggerService,
        DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService                             = $chargeService;
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
     * @throws InvalidStatusException
     * @throws TransactionCreationException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function execute(Command $command): TransactionCommandHttpDTO
    {
        try {
            if (!$command instanceof PerformRocketgateSimplifiedCompleteThreeDCommand) {
                throw new InvalidCommandException(PerformRocketgateSimplifiedCompleteThreeDCommand::class, $command);
            }

            Log::info('Begin processing the simplified complete threeD transaction');

            /**
             * @var ChargeTransaction
             */
            $previousTransaction = $this->retrievePreviousTransaction($command->transactionId());
            $previousTransaction->updateTransactionWith3D(true);

            // Perform the simplified complete threeD transaction
            $billerResponse = $this->chargeService->simplifiedCompleteThreeD(
                $previousTransaction,
                $command->queryString()
            );

            // Update transaction
            $previousTransaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

            /** TODO done to align with the new TS */
            $rgBillerInteractions = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
                $previousTransaction->billerInteractions(),
                $previousTransaction->threedsVersion() > 0 ? true : false
            );

            $previousTransaction->billerTransactions = $rgBillerInteractions->getEncodedBillerTransactions();
            if ($billerResponse->approved() || $billerResponse->pending()) {
                $previousTransaction->subsequentOperationFields = $rgBillerInteractions->getEncodedSubsequentOperationFields();
            }

            // Persist transaction entity
            $this->repository->update($previousTransaction);

            // Write BiLogger event
            $event = new TransactionUpdated(
                $previousTransaction,
                $billerResponse,
                RocketGateBillerSettings::ROCKETGATE,
                BillerSettings::ACTION_UPDATE
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

            // Return DTO
            return $this->dtoAssembler->assemble($previousTransaction, ($errorClassification ?? null));
        } catch (TransactionNotFoundException | InvalidStatusException | InvalidPayloadException | InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }

    /**
     * @param string $transactionId Transaction Id
     * @return ChargeTransaction
     * @throws InvalidStatusException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     * @throws Exception
     */
    private function retrievePreviousTransaction(string $transactionId): ChargeTransaction
    {
        $previousTransaction = $this->repository->findById(
            (string) TransactionId::createFromString($transactionId)
        );

        if (!$previousTransaction instanceof ChargeTransaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if (!$previousTransaction->status() instanceof Pending) {
            throw new InvalidStatusException();
        }

        return $previousTransaction;
    }
}
