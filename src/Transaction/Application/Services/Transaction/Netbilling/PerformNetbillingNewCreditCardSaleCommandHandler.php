<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Log;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaNetbilling;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

class PerformNetbillingNewCreditCardSaleCommandHandler extends BaseCommandHandler
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
     * PerformNetbillingNewCreditCardSaleCommandHandler constructor.
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              The dto assembler.
     * @param TransactionRepository                     $repository                                The repository object.
     * @param ChargeService                             $chargeService                             The service to call netbilling.
     * @param BILoggerService                           $biLoggerService                           The BI logger.
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
     * @param Command $command The create transaction command
     * @return mixed|void
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws LoggerException
     * @throws NetbillingServiceException
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformNetbillingNewCreditCardSaleCommand) {
                throw new InvalidCommandException(PerformNetbillingNewCreditCardSaleCommand::class, $command);
            }

            Log::info('Begin processing transaction with netbilling');

            // Create entity
            $transaction = $this->createNetbillingTransaction($command);

            $billerResponse = $this->chargeService->chargeNewCreditCard($transaction);

            $transaction->updateTransactionFromNetbillingResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new ChargeTransactionCreated($transaction, $billerResponse, NetbillingBillerSettings::NETBILLING);
            $this->biLoggerService->write($event);

            /** TODO done to align with the new TS */
            list($billerTransactions, $subsequentOperationFields)
                = RetrieveNetbillingChargeTransactionReturnType::getBillerInteractionsData($transaction);

            $transaction->billerTransactions
                = RetrieveNetbillingChargeTransactionReturnType::getEncodedBillerTransactions($billerTransactions);

            if ($billerResponse->approved() || $billerResponse->pending()) {
                $transaction->subsequentOperationFields = $subsequentOperationFields;
            }

            // Persist updated transaction entity
            $this->repository->update($transaction);

            // Add error classification based on the biller response.
            if ($billerResponse->declined() || $billerResponse->aborted()) {
                // Build mapping criteria based on biller response.
                $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

                // Get extra data to build the error classification.
                $extraData = $this->declinedBillerResponseExtraDataRepository->retrieve($mappingCriteria);

                $errorClassification = new ErrorClassification($mappingCriteria, $extraData);

                Log::info('ErrorClassification', $errorClassification->toArray());

                // TODO: Create a new interface for RocketgateTransactionDTOAssembler to have the errorClassification.
                return $this->dtoAssembler->assemble($transaction, ($errorClassification ?? null));
            }

            // Return DTO
            /* @var TransactionDTOAssembler */
            return $this->dtoAssembler->assemble($transaction);

        } catch (InvalidPayloadException | \InvalidArgumentException | NetbillingServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
