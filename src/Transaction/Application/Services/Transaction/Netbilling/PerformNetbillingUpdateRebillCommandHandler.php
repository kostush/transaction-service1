<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\CreateUpdateRebillTransactionTrait;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\PreviousTransactionCorruptedDataException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaNetbilling;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

/**
 * Class PerformNetbillingUpdateRebillCommandHandler
 * @package ProBillerNG\Transaction\Application\Services\Transaction\Netbilling
 */
class PerformNetbillingUpdateRebillCommandHandler extends BaseCommandHandler
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
     * PerformNetbillingUpdateRebillCommandHandler constructor.
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              DTO
     * @param TransactionRepository                     $repository                                Repository
     * @param UpdateRebillService                       $updateRebillService                       Update Rebill Service
     * @param BILoggerService                           $biLoggerService                           Bi Logger
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
     * Executes a command
     *
     * @param Command $command Command
     *
     * @return mixed
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws TransactionNotFoundException|NetbillingServiceException
     * @throws Exception
     * @throws PreviousTransactionCorruptedDataException
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformNetbillingUpdateRebillCommand) {
                throw new InvalidCommandException(PerformNetbillingUpdateRebillCommand::class, $command);
            }

            Log::info('Begin processing update rebill netbilling transaction');

            if (empty($command->transactionId())) {
                throw new MissingTransactionInformationException('transactionId');
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->transactionId());
            }

            if ($previousTransaction->billerName() !== NetbillingBillerSettings::NETBILLING) {
                throw new InvalidTransactionInformationException('A non-Netbilling transaction id was provided');
            }

            // Create entity
            $transaction = $this->createNetbillingUpdateRebillTransaction($command, $previousTransaction);

            $billerResponse = $this->updateRebillService->update($transaction);

            $transaction->updateTransactionFromNetbillingResponse($billerResponse);

            /** TODO done to align with the new TS */
            list($billerTransactions, $subsequentOperationFields)
                = RetrieveNetbillingChargeTransactionReturnType::getBillerInteractionsData($transaction);

            $transaction->billerTransactions
                = RetrieveNetbillingChargeTransactionReturnType::getEncodedBillerTransactions($billerTransactions);

            if ($billerResponse->approved() || $billerResponse->pending()) {
                $transaction->subsequentOperationFields = $subsequentOperationFields;
            }

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new RebillUpdateTransactionCreated(
                $transaction,
                $billerResponse,
                NetbillingBillerSettings::NETBILLING,
                NetbillingBillerSettings::ACTION_UPDATE
            );
            $this->biLoggerService->write($event);

            // Add error classification based on the biller response.
            if ($billerResponse->declined() || $billerResponse->aborted()) {
                // Build mapping criteria based on biller response.
                $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

                // Get extra data to build the error classification.
                $extraData = $this->declinedBillerResponseExtraDataRepository->retrieve($mappingCriteria);

                $errorClassification = new ErrorClassification($mappingCriteria, $extraData);

                Log::info('ErrorClassification', $errorClassification->toArray());
            }

            // Return DTO
            return $this->dtoAssembler->assemble($transaction, ($errorClassification ?? null));
        } catch (PreviousTransactionCorruptedDataException | InvalidPayloadException | TransactionNotFoundException | \InvalidArgumentException | NetbillingServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
