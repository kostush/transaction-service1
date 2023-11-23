<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\InMemoryRepository;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;

class PerformRocketgateNewCreditCardSaleCommandHandler extends BaseCommandHandler
{
    /**
     * @var ChargeThreeDService
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
     * @var InMemoryRepository
     */
    private $inMemoryRepository;

    /**
     * PerformRocketgateSaleCommandHandler constructor.
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              The dto assembler
     * @param TransactionRepository                     $repository                                The repository object
     * @param ChargeThreeDService                       $chargeService                             The charge service
     * @param BILoggerService                           $biLoggerService                           The BiLogger
     * @param DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository Repository
     * @param InMemoryRepository                        $inMemoryRepository                        Redis interface
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        ChargeThreeDService $chargeService,
        BILoggerService $biLoggerService,
        DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository,
        InMemoryRepository $inMemoryRepository
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService                             = $chargeService;
        $this->biLoggerService                           = $biLoggerService;
        $this->declinedBillerResponseExtraDataRepository = $declinedBillerResponseExtraDataRepository;
        $this->inMemoryRepository                        = $inMemoryRepository;
    }

    /**
     * Execute the create transaction command
     *
     * @param Command $command The create transaction command
     *
     * @return TransactionCommandHttpDTO
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws Exception
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformRocketgateNewCreditCardSaleCommand) {
                throw new InvalidCommandException(PerformRocketgateNewCreditCardSaleCommand::class, $command);
            }

            Log::info('Begin processing transaction' . ($command->useThreeD() ? ' with 3DS' : ''));

            // Create entity
            $transaction = $this->createRocketgateTransaction($command);

            // Perform charge
            $billerResponse = $this->chargeService->chargeNewCreditCard(
                $transaction
            );

            // Set CVV into Redis for later use ( Complete step )
            // Since the same handler is used for non-3DS transactions we need to make sure we store the CVV only
            // when authentication is required and the status is pending.
            if ($billerResponse->threeDsAuthIsRequired() && $billerResponse->pending()) {
                Log::info("CVVStore on 3DS v1 authentication.");
                $this->inMemoryRepository->storeCvv(
                    (string) $transaction->transactionId(),
                    RocketGateBillerSettings::ROCKETGATE,
                    $command->payment()->information()->cvv()
                );
            }

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new ChargeTransactionCreated($transaction, $billerResponse, RocketGateBillerSettings::ROCKETGATE);
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

            // If the transaction is NSF and the site supports NSF transactions, we are going to call
            // the charge service cardUpload. Card upload functionality was separated into a different call
            // from chargeNewCreditCard, so that we make a new transaction only after we make sure we added
            // the error classification based on the first biller response.
            if ($billerResponse->isNsfTransaction() && env('NSF_FLOW_ENABLED') && $command->isNSFSupported()) {
                // Perform card upload operation
                $this->chargeService->cardUpload($transaction);
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

            $this->repository->update($transaction);

            // TODO: Create a new interface for RocketgateTransactionDTOAssembler to have the errorClassification.
            return $this->dtoAssembler->assemble($transaction, ($errorClassification ?? null));
        } catch (InvalidPayloadException | \InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
