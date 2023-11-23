<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
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
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;

class PerformRocketgateExistingCreditCardSaleCommandHandler extends BaseCommandHandler
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
     * PerformRocketgateSaleCommandHandler constructor.
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              The dto assembler
     * @param TransactionRepository                     $repository                                The repository object
     * @param ChargeThreeDService                       $chargeService                             The charge service
     * @param BILoggerService                           $biLoggerService                           The BiLogger
     * @param DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository Repository
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        ChargeThreeDService $chargeService,
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
     * @throws TransactionCreationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformRocketgateExistingCreditCardSaleCommand) {
                throw new InvalidCommandException(PerformRocketgateExistingCreditCardSaleCommand::class, $command);
            }

            Log::info('Begin processing transaction');

            // Create entity
            $transaction = $this->createRocketgateTransaction($command);

            // Perform charge
            /** @var RocketgateBillerResponse $billerResponse */
            $billerResponse = $this->chargeService->chargeExistingCreditCard(
                $transaction
            );

            // Update transaction
            $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

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

                // TODO: Create a new interface for RocketgateTransactionDTOAssembler to have the errorClassification.
                return $this->dtoAssembler->assemble($transaction, ($errorClassification ?? null));
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
            return $this->dtoAssembler->assemble($transaction);
        } catch (InvalidPayloadException | \InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
