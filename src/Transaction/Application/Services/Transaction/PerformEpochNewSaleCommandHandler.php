<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\EpochService;

class PerformEpochNewSaleCommandHandler extends BaseCommandHandler
{
    /**
     * @var EpochService
     */
    private $chargeService;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;

    /**
     * PerformRocketgateSaleCommandHandler constructor.
     * @param EpochNewSaleTransactionDTOAssembler $dtoAssembler    The dto assembler
     * @param TransactionRepository               $repository      The repository object
     * @param EpochService                        $epochService    The charge service
     * @param BILoggerService                     $biLoggerService The BiLogger
     */
    public function __construct(
        EpochNewSaleTransactionDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        EpochService $epochService,
        BILoggerService $biLoggerService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService   = $epochService;
        $this->biLoggerService = $biLoggerService;
    }

    /**
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
            if (!$command instanceof PerformEpochNewSaleCommand) {
                throw new InvalidCommandException(PerformEpochNewSaleCommand::class, $command);
            }

            Log::info('Begin processing epoch transaction');

            // Create Transaction collection
            $transactions = $this->createEpochTransactions($command);

            // Perform charge
            $billerResponse = $this->chargeService->chargeNewSale(
                $transactions,
                $this->extractTaxesFromCommand($command),
                $command->sessionId(),
                $command->member()
            );

            foreach ($transactions as $transaction) {
                // Update transaction
                /** @var ChargeTransaction $transaction */
                $transaction->updateEpochTransactionFromBillerResponse($billerResponse);

                // Persist transaction entity
                $this->repository->add($transaction);

                //Write BiLogger event
                $event = new ChargeTransactionCreated($transaction, $billerResponse, EpochBillerSettings::EPOCH);
                $this->biLoggerService->write($event);

                if (!$transaction->status()->pending()) {
                    // skip adding the crossales transactions to db
                    break;
                }
            }

            // Return DTO
            return $this->dtoAssembler->assemble($transactions);
        } catch (InvalidPayloadException | \InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }

    /**
     * @param Command $command The create transaction command
     * @return array
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    private function createEpochTransactions(Command $command): array
    {
        /** @var PerformEpochNewSaleCommand $command */
        $crossSalesTransactions[] = $this->createEpochTransaction($command);

        foreach ($command->crossSales() as $crossSale) {
            $rebill = null;

            if (!empty($crossSale['rebill'])) {
                $rebill = new Rebill(
                    $crossSale['rebill']['amount'],
                    $crossSale['rebill']['frequency'],
                    $crossSale['rebill']['start'] ?? 0
                );
            }

            $crossSalesTransactions[] = $this->createEpochTransaction(
                new PerformEpochNewSaleCommand(
                    $command->sessionId(),
                    (string) $crossSale['siteId'],
                    (string) $crossSale['siteName'],
                    (float) $crossSale['amount'],
                    $command->currency(),
                    $command->payment(),
                    [],
                    $crossSale['tax'] ?? [],
                    $command->billerFields(),
                    $command->member(),
                    $rebill
                )
            );
        }

        return $crossSalesTransactions;
    }

    /**
     * @param Command $command The create transaction command
     * @return array
     */
    private function extractTaxesFromCommand(Command $command): array
    {
        /** @var PerformEpochNewSaleCommand $command */
        $taxes[] = $command->tax();

        foreach ($command->crossSales() as $crossSale) {
            $taxes[] = $crossSale['tax'] ?? [];
        }

        return $taxes;
    }
}
