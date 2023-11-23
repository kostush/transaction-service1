<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use InvalidArgumentException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionLookupException;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPreviousTransactionStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\InMemoryRepository;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoService;

class RocketgateLookupThreeDsTwoCommandHandler extends BaseCommandHandler
{
    /**
     * @var LookupThreeDsTwoService
     */
    private $lookupService;

    /**
     * @var InMemoryRepository
     */
    private $inMemoryRepository;

    /**
     * @var DeclinedBillerResponseExtraDataRepository
     */
    protected $declinedBillerResponseExtraDataRepository;

    /**
     * RocketgateLookupThreeDsTwoCommandHandler constructor.
     *
     * @param HttpCommandDTOAssembler                   $dtoAssembler                              DTO Assembler
     * @param TransactionRepository                     $repository                                Repository
     * @param LookupThreeDsTwoService                   $lookupService                             Lookup service
     * @param InMemoryRepository                        $inMemoryRepository                        Redis interface
     * @param DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository Repository to get error classification
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        LookupThreeDsTwoService $lookupService,
        InMemoryRepository $inMemoryRepository,
        DeclinedBillerResponseExtraDataRepository $declinedBillerResponseExtraDataRepository
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->lookupService                             = $lookupService;
        $this->inMemoryRepository                        = $inMemoryRepository;
        $this->declinedBillerResponseExtraDataRepository = $declinedBillerResponseExtraDataRepository;
    }

    /**
     * Executes a command
     *
     * @param Command $command Command
     * @return mixed
     * @throws InvalidPayloadException
     * @throws TransactionLookupException
     * @throws TransactionNotFoundException
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof LookupThreeDsTwoCommand) {
                throw new InvalidCommandException(LookupThreeDsTwoCommand::class, $command);
            }

            Log::info('Beginning the lookup process for transaction: ' . $command->previousTransactionId());

            /** @var ChargeTransaction $previousTransaction */
            $previousTransaction = $this->repository->findById($command->previousTransactionId());

            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->previousTransactionId());
            }

            if (!$previousTransaction->status() instanceof Pending) {
                throw new InvalidPreviousTransactionStatusException((string) $previousTransaction->status());
            }

            $responsePayload = json_decode($previousTransaction->responsePayload(), true, 512, JSON_THROW_ON_ERROR);
            $merchantAccount = $responsePayload['merchantAccount'];

            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->previousTransactionId());
            }

            $creditCardData = CreditCardInformation::create(
                $previousTransaction->paymentInformation()->cvv2Check(), //should this be part of the payload? cvv2Check
                CreditCardNumber::create($command->payment()->information()->number()),
                $previousTransaction->paymentInformation()->creditCardOwner(),
                $previousTransaction->paymentInformation()->creditCardBillingAddress(),
                $command->payment()->information()->cvv(),
                $command->payment()->information()->expirationMonth(),
                $command->payment()->information()->expirationYear(),
            );

            $previousTransaction->setPaymentInformation($creditCardData);

            $transactionAfterLookup = $this->lookupService->performTransaction(
                $previousTransaction,
                $command->payment()->information()->number(),
                (string) $command->payment()->information()->expirationMonth(),
                (string) $command->payment()->information()->expirationYear(),
                $command->payment()->information()->cvv(),
                $command->deviceFingerprintingId(),
                $command->redirectUrl(),
                $merchantAccount,
                $command->isNSFSupported()
            );

            // Set CVV into Redis for later use ( Complete step )
            if ($transactionAfterLookup->with3D() && $transactionAfterLookup->status()->pending()) {
                $this->inMemoryRepository->storeCvv(
                    (string) $transactionAfterLookup->transactionId(),
                    $transactionAfterLookup->billerName(),
                    $command->payment()->information()->cvv()
                );
            }

            /** TODO done to align with the new TS */
            /** @var ChargeTransaction $transactionAfterLookup */
            $rgBillerInteractions = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
                $transactionAfterLookup->billerInteractions(),
                $transactionAfterLookup->threedsVersion() > 0 ? true : false
            );

            $transactionAfterLookup->billerTransactions = $rgBillerInteractions->getEncodedBillerTransactions();
            if ($transactionAfterLookup->status()->approved() || $transactionAfterLookup->status()->pending()) {
                $transactionAfterLookup->subsequentOperationFields = $rgBillerInteractions->getEncodedSubsequentOperationFields();
            }

            // Add error classification for NFS declined transaction when we retry with bypass 3ds
            $mappingCriteria = $transactionAfterLookup->errorMappingCriteria;

            if ($mappingCriteria != null && ($transactionAfterLookup->status()->declined() || $transactionAfterLookup->status()->aborted())) {
                // Get extra data to build the error classification.
                $extraData = $this->declinedBillerResponseExtraDataRepository->retrieve($mappingCriteria);

                $errorClassification = new ErrorClassification($mappingCriteria, $extraData);

                Log::info('ErrorClassification', $errorClassification->toArray());
            }

            // Persist transaction entity
            $this->repository->update($transactionAfterLookup);

            // Return DTO
            return $this->dtoAssembler->assemble($transactionAfterLookup, ($errorClassification ?? null));
        } catch (InvalidPayloadException | InvalidArgumentException | TransactionNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionLookupException($e);
        }
    }
}
