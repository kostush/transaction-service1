<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Epoch\Domain\Model\PostbackTranslateResponse as EpochPostbackResponse;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\EpochService;

class AddBillerInteractionForJoinOnEpochCommandHandler extends BaseCommandHandler
{
    /**
     * @var EpochService
     */
    protected $epochService;

    /**
     * @var BILoggerService
     */
    protected $biService;

    /**
     * @param TransactionRepository                    $repository   Repository
     * @param EpochJoinPostbackTransactionDTOAssembler $dtoAssembler DTO Assembler
     * @param EpochService                             $epochService Epoch Service
     * @param BILoggerService                          $biService    BI Service
     */
    public function __construct(
        TransactionRepository $repository,
        EpochJoinPostbackTransactionDTOAssembler $dtoAssembler,
        EpochService $epochService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService    = $biService;
        $this->epochService = $epochService;
    }

    /**
     * @param Command $command Command
     * @return EpochJoinPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): EpochJoinPostbackCommandHttpDTO
    {
        try {
            if (!$command instanceof AddBillerInteractionForJoinOnEpochCommand) {
                throw new InvalidCommandException(AddBillerInteractionForJoinOnEpochCommand::class, $command);
            }

            /** @var ChargeTransaction $transaction */
            $transaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            $this->validateTransactionData($transaction, $command->transactionId());

            $billerResponse = $this->epochService->translatePostback(
                $command->payload(),
                EpochPostbackResponse::CHARGE_TYPE_JOIN,
                $transaction->billerChargeSettings()->clientVerificationKey()
            );
            // $transaction->billerChargeSettings() returns EpochBillerChargeSettings

            $transaction->updateEpochTransactionFromBillerResponse($billerResponse);

            $this->repository->update($transaction);

            $this->biService->write(
                new TransactionUpdated(
                    $transaction,
                    $billerResponse,
                    EpochBillerSettings::EPOCH,
                    BillerSettings::ACTION_POSTBACK
                )
            );

            // Return DTO
            return $this->dtoAssembler->assemble($transaction, $billerResponse);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Transaction|null $transaction   Transaction Entity
     * @param string           $transactionId Transaction Id
     * @return void
     * @throws InvalidBillerException
     * @throws PostbackAlreadyProcessedException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function validateTransactionData(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if ($transaction->billerName() !== EpochBillerSettings::EPOCH) {
            throw new InvalidBillerException(EpochBillerSettings::EPOCH);
        }

        if (!$transaction->status()->pending()) {
            throw new PostbackAlreadyProcessedException($transactionId);
        }
    }
}
