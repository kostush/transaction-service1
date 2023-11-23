<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\QyssoService;

class AddBillerInteractionForJoinOnQyssoCommandHandler extends BaseCommandHandler
{
    /**
     * @var QyssoService
     */
    protected $qyssoService;

    /**
     * @var BILoggerService
     */
    protected $biService;

    /**
     * AddBillerInteractionForJoinOnQyssoCommandHandler constructor.
     * @param TransactionRepository                    $repository   Repository
     * @param QyssoJoinPostbackTransactionDTOAssembler $dtoAssembler DTO Assembler
     * @param QyssoService                             $qyssoService Qysso Service
     * @param BILoggerService                          $biService    BI Service
     */
    public function __construct(
        TransactionRepository $repository,
        QyssoJoinPostbackTransactionDTOAssembler $dtoAssembler,
        QyssoService $qyssoService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService    = $biService;
        $this->qyssoService = $qyssoService;
    }

    /**
     * @param Command $command Command
     * @return QyssoJoinPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): QyssoJoinPostbackCommandHttpDTO
    {
        try {
            if (!$command instanceof AddBillerInteractionForJoinOnQyssoCommand) {
                throw new InvalidCommandException(AddBillerInteractionForJoinOnQyssoCommand::class, $command);
            }

            /** @var ChargeTransaction $transaction */
            $transaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            $this->validateTransactionData($transaction, $command->transactionId());

            $billerResponse = $this->qyssoService->translatePostback(
                $command->payload(),
                $transaction->billerChargeSettings()->personalHashKey()
            );

            $transaction->updateQyssoTransactionFromBillerResponse($billerResponse);

            /** TODO done to align with the new TS */
            $qyssoChargeTransactionReturnType
                = RetrieveQyssoChargeTransactionReturnType::createFromEntity($transaction);

            $transaction->billerTransactions = $qyssoChargeTransactionReturnType->getEncodedBillerTransactions(
                $transaction->billerTransactions
            );

            $this->repository->update($transaction);

            $this->biService->write(
                new TransactionUpdated(
                    $transaction,
                    $billerResponse,
                    QyssoBillerSettings::QYSSO,
                    BillerSettings::ACTION_POSTBACK
                )
            );

            return $this->dtoAssembler->assemble($transaction);
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
     * @throws Exception
     */
    protected function validateTransactionData(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if ($transaction->billerName() !== QyssoBillerSettings::QYSSO) {
            throw new InvalidBillerException(QyssoBillerSettings::QYSSO);
        }

        if (!$transaction->status()->pending()) {
            throw new PostbackAlreadyProcessedException($transactionId);
        }
    }
}
