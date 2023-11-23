<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use InvalidArgumentException;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Qysso\Domain\Model\Exception\InvalidFieldException;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\QyssoService;
use Throwable;

class PerformQyssoNewSaleCommandHandler extends BaseCommandHandler
{
    /**
     * @var BILoggerService
     */
    protected $biLoggerService;
    /**
     * @var QyssoService
     */
    private $chargeService;

    /**
     * PerformQyssoNewSaleCommandHandler constructor.
     * @param QyssoNewSaleTransactionDTOAssembler $dtoAssembler    The dto assembler
     * @param TransactionRepository               $repository      The repository object
     * @param QyssoService                        $qyssoService    The charge service
     * @param BILoggerService                     $biLoggerService The BiLogger
     */
    public function __construct(
        QyssoNewSaleTransactionDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        QyssoService $qyssoService,
        BILoggerService $biLoggerService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService   = $qyssoService;
        $this->biLoggerService = $biLoggerService;
    }

    /**
     * @param Command $command The create transaction command
     *
     * @return TransactionCommandHttpDTO
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws InvalidFieldException
     * @throws Exception
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformQyssoNewSaleCommand) {
                throw new InvalidCommandException(PerformQyssoNewSaleCommand::class, $command);
            }

            Log::info('Begin processing qysso transaction');

            // Create Transaction collection
            $transactions = $this->createQyssoTransactions($command);

            // Perform charge
            $billerResponse = $this->chargeService->chargeNewSale(
                $transactions,
                $command->tax(),
                $command->sessionId(),
                $command->clientIp(),
                $command->member()
            );

            foreach ($transactions as $transaction) {
                // Update transaction
                $transaction->updateQyssoTransactionFromBillerResponse($billerResponse);

                /** TODO done to align with the new TS */
                $qyssoChargeTransactionReturnType
                    = RetrieveQyssoChargeTransactionReturnType::createFromEntity($transaction);

                $transaction->billerTransactions = $qyssoChargeTransactionReturnType->getEncodedBillerTransactions();

                // Persist transaction entity
                $this->repository->add($transaction);

                //Write BiLogger event
                $event = new ChargeTransactionCreated($transaction, $billerResponse, QyssoBillerSettings::QYSSO);
                $this->biLoggerService->write($event);
            }

            return $this->dtoAssembler->assemble($transactions);
        } catch (InvalidFieldException | InvalidPayloadException | InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }

    /**
     * @param Command $command The create transaction command
     *
     * @return ChargeTransaction[]
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    private function createQyssoTransactions(Command $command): array
    {
        /** @var PerformEpochNewSaleCommand $command */
        return [$this->createQyssoTransaction($command)];
    }
}
