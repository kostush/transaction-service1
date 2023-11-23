<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Legacy;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingSiteIdForCrossSaleException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\LegacyPostbackResponseService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;

class AddBillerInteractionForJoinOnLegacyCommandHandler extends BaseCommandHandler
{
    /**
     * @var BILoggerService
     */
    private $biService;

    /**
     * @var LegacyPostbackResponseService
     */
    private $legacyService;

    /**
     * PerformLegacyNewSaleCommandHandler constructor.
     * @param LegacyJoinPostbackTransactionDTOAssembler $dtoAssembler  DTO
     * @param TransactionRepository                     $repository    Repository
     * @param LegacyPostbackResponseService             $legacyService Legacy Service
     * @param BILoggerService                           $biService     BI Log service
     */
    public function __construct(
        LegacyJoinPostbackTransactionDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        LegacyPostbackResponseService $legacyService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);
        $this->biService     = $biService;
        $this->legacyService = $legacyService;
    }

    /**
     * @param Command $command Command
     * @return mixed|void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCommandException
     * @throws InvalidTransactionInformationException
     * @throws TransactionNotFoundException
     * @throws MissingSiteIdForCrossSaleException
     */
    public function execute(Command $command)
    {
        /** @var AddBillerInteractionForJoinOnLegacyCommand $command */
        $this->assertIsValidCommand($command);

        /** @var ChargeTransaction $transaction */
        $transaction = $this->repository->findById(
            (string) TransactionId::createFromString($command->transactionId())
        );

        $billerResponse = $this->legacyService->translate(
            $command->payload(),
            $command->type(),
            $command->statusCode()
        );

        $this->validateTransactionData(
            $command->transactionId(),
            $billerResponse->isCrossSale(),
            $transaction,
            $command->siteId()
        );

        //There is no declined cross sale
        if ($billerResponse->isCrossSale() && $billerResponse->approved()) {
            Log::info('Creating cross sale transaction');
            $transaction = ChargeTransaction::createLegacyCrossSaleTransaction(
                $transaction,
                $billerResponse,
                $command->siteId()
            );
        }

        if ($transaction->isNotProcessed()) {
            Log::info(
                'Updating transaction because it is not processed',
                ['currentStatus' => (string) $transaction->status()]
            );
            $this->updateTransactionAndSendEvents($transaction, $billerResponse);
        } else {
            Log::info(
                'Transaction not updated because it was already processed',
                ['status' => (string) $transaction->status()]
            );
        }

        return $this->dtoAssembler->assemble($transaction);
    }

    /**
     * @param ChargeTransaction            $transaction    Transaction
     * @param LegacyPostbackBillerResponse $billerResponse Biller Response
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @return void
     */
    private function updateTransactionAndSendEvents(
        ChargeTransaction $transaction,
        LegacyPostbackBillerResponse $billerResponse
    ): void {

        $transaction->updateLegacyTransactionFromBillerResponse($billerResponse);

        $this->repository->update($transaction);

        $this->biService->write(
            new TransactionUpdated(
                $transaction,
                $billerResponse,
                $transaction->billerChargeSettings()->billerName(),
                $billerResponse->type()
            )
        );
    }

    /**
     * @param Command $command Command
     * @return void
     * @throws InvalidCommandException
     * @throws Exception
     */
    private function assertIsValidCommand(Command $command): void
    {
        if (!($command instanceof AddBillerInteractionForJoinOnLegacyCommand)) {
            throw new InvalidCommandException(AddBillerInteractionForJoinOnLegacyCommand::class, $command);
        }
    }

    /**
     * @param string           $transactionId TransactionId
     * @param bool             $isCrossSale   Is cross sale
     * @param Transaction|null $transaction   Transaction Entity
     * @param string|null      $siteId        Site Id
     * @return void
     * @throws Exception
     * @throws MissingSiteIdForCrossSaleException
     * @throws TransactionNotFoundException
     */
    protected function validateTransactionData(
        string $transactionId,
        bool $isCrossSale,
        ?Transaction $transaction,
        ?string $siteId
    ): void {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if ($isCrossSale && $siteId === null) {
            throw new MissingSiteIdForCrossSaleException();
        }
    }
}
