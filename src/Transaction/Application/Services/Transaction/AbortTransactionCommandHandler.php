<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\AbortTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;

class AbortTransactionCommandHandler extends BaseCommandHandler
{

    /**
     * @var BILoggerService
     */
    protected $biService;

    /**
     * AbortTransactionCommandHandler constructor.
     * @param TransactionRepository        $repository     Repository
     * @param AbortTransactionDTOAssembler $dtoAssembler   DTO Assembler
     * @param BILoggerService              $biService      BI Service
     */
    public function __construct(
        TransactionRepository $repository,
        AbortTransactionDTOAssembler $dtoAssembler,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService = $biService;
    }

    /**
     * @param Command $command Command
     * @return array
     * @throws \Exception
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof AbortTransactionCommand) {
                throw new InvalidCommandException(AbortTransactionCommand::class, $command);
            }

            $transaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            $this->validateTransactionData($transaction, $command->transactionId());

            $transaction->abort();

            $this->repository->update($transaction);

            $this->biService->write(
                new TransactionUpdated(
                    $transaction,
                    null,
                    $transaction->billerChargeSettings()->billerName(),
                    BillerSettings::ACTION_ABORT
                )
            );

            // Return DTO
            return $this->dtoAssembler->assemble($transaction);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Transaction|null $transaction   Transaction Entity
     * @param string           $transactionId Transaction Id
     * @throws TransactionNotFoundException
     * @throws TransactionAlreadyProcessedException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    protected function validateTransactionData(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if (!$transaction->status()->pending()) {
            throw new TransactionAlreadyProcessedException($transactionId);
        }
    }
}
