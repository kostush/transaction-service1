<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayCancelRebillDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionShouldBeApprovedException;
use ProBillerNG\Transaction\Domain\Model\Exception\RebillNotSetException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;

class PumapayCancelRebillCommandHandler extends BaseCommandHandler
{
    /**
     * @var PumapayService
     */
    protected $pumapayService;

    /**
     * @var BILoggerService
     */
    protected $biService;

    /**
     * PumapayCancelRebillCommandHandler constructor.
     * @param TransactionRepository           $repository     Repository
     * @param PumapayCancelRebillDTOAssembler $dtoAssembler   DTO Assembler
     * @param PumapayService                  $pumapayService Pumapay Service
     * @param BILoggerService                 $biService      BI Service
     */
    public function __construct(
        TransactionRepository $repository,
        PumapayCancelRebillDTOAssembler $dtoAssembler,
        PumapayService $pumapayService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->pumapayService = $pumapayService;
        $this->biService      = $biService;
    }

    /**
     * @param Command $command Command
     * @return mixed|void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws InvalidCommandException
     * @throws InvalidTransactionInformationException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     */
    public function execute(Command $command)
    {
        if (!$command instanceof PumapayCancelRebillCommand) {
            throw new InvalidCommandException(PumapayCancelRebillCommand::class, $command);
        }

        $previousTransaction = $this->repository->findById(
            (string) TransactionId::createFromString($command->transactionId())
        );

        $this->validateTransactionData($previousTransaction, $command->transactionId());

        Log::info('Canceling rebill for transaction: ' . (string) $previousTransaction->transactionId());

        // create new transaction
        $cancelRebillTransaction = RebillUpdateTransaction::createCancelRebillPumapayTransaction(
            $previousTransaction,
            $command->businessId(),
            $command->businessModel(),
            $command->apiKey()
        );

        // Perform suspend
        $billerResponse = $this->pumapayService->cancelRebill(
            $previousTransaction,
            $command->businessId(),
            $command->apiKey()
        );

        // Update transaction
        $cancelRebillTransaction->updatePumapayTransactionFromBillerResponse($billerResponse);

        // Persist transaction entity
        $this->repository->add($cancelRebillTransaction);

        $this->biService->write(
            new RebillUpdateTransactionCreated(
                $cancelRebillTransaction,
                $billerResponse,
                PumaPayBillerSettings::PUMAPAY,
                BillerSettings::ACTION_CANCEL
            )
        );

        // Return DTO
        return $this->dtoAssembler->assemble($cancelRebillTransaction);
    }

    /**
     * @param Transaction|null $transaction   Transaction
     * @param string           $transactionId Transaction Id
     * @return void
     * @throws InvalidBillerNameException
     * @throws InvalidTransactionTypeException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws TransactionNotFoundException
     * @throws Exception
     */
    protected function validateTransactionData(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if (!$transaction instanceof ChargeTransaction) {
            throw new InvalidTransactionTypeException(ChargeTransaction::TYPE);
        }

        if ($transaction->billerName() !== PumaPayBillerSettings::PUMAPAY) {
            throw new InvalidBillerNameException(PumaPayBillerSettings::PUMAPAY, $transaction->billerName());
        }

        if (!$transaction->status()->approved()) {
            throw new PreviousTransactionShouldBeApprovedException($transactionId);
        }

        if ($transaction->chargeInformation() instanceof ChargeInformation
            && $transaction->chargeInformation()->rebill() === null
        ) {
            throw new RebillNotSetException($transactionId);
        }
    }
}
