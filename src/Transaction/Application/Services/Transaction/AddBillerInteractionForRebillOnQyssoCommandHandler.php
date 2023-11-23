<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoRebillPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoRebillPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoRebillTransactionReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionShouldBeApprovedException;
use ProBillerNG\Transaction\Domain\Model\Exception\RebillNotSetException;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\QyssoService;

class AddBillerInteractionForRebillOnQyssoCommandHandler extends BaseCommandHandler
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
     * AddBillerInteractionForRebillOnQyssoCommandHandler constructor.
     * @param TransactionRepository                      $repository   Repository
     * @param QyssoRebillPostbackTransactionDTOAssembler $dtoAssembler DTO assembler
     * @param QyssoService                               $qyssoService Qysso service
     * @param BILoggerService                            $biService    BI service
     */
    public function __construct(
        TransactionRepository $repository,
        QyssoRebillPostbackTransactionDTOAssembler $dtoAssembler,
        QyssoService $qyssoService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService    = $biService;
        $this->qyssoService = $qyssoService;
    }

    /**
     * @param Command $command Command
     * @return QyssoRebillPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): QyssoRebillPostbackCommandHttpDTO
    {
        try {
            if (!$command instanceof AddBillerInteractionForRebillOnQyssoCommand) {
                throw new InvalidCommandException(AddBillerInteractionForRebillOnQyssoCommand::class, $command);
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->previousTransactionId())
            );

            $this->validatePreviousTransactionData($previousTransaction, $command->previousTransactionId());

            // create a pending rebill transaction
            $rebillTransaction = RebillUpdateTransaction::createQyssoRebillUpdateTransaction($previousTransaction);

            $billerResponse = $this->qyssoService->translatePostback(
                json_encode($command->payload()),
                $previousTransaction->billerChargeSettings()->personalHashKey(),
                PostbackResponse::CHARGE_TYPE_REBILL
            );

            $rebillTransaction->updateQyssoTransactionFromBillerResponse($billerResponse);

            /** TODO done to align with the new TS */
            $qyssoChargeTransactionReturnType
                = RetrieveQyssoRebillTransactionReturnType::createFromEntity($rebillTransaction);

            $rebillTransaction->billerTransactions = $qyssoChargeTransactionReturnType->getEncodedBillerTransactions();

            // Persist transaction entity
            $this->repository->add($rebillTransaction);

            $this->biService->write(
                new RebillUpdateTransactionCreated(
                    $rebillTransaction,
                    $billerResponse,
                    QyssoBillerSettings::QYSSO,
                    BillerSettings::ACTION_POSTBACK
                )
            );

            // Return DTO
            return $this->dtoAssembler->assemble($rebillTransaction);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Transaction|null $previousTransaction   Previous transaction
     * @param string           $previousTransactionId Previous transaction Id
     * @return void
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws PreviousTransactionNotFoundException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     */
    protected function validatePreviousTransactionData(
        ?Transaction $previousTransaction,
        string $previousTransactionId
    ): void {
        if (!$previousTransaction instanceof Transaction) {
            throw new PreviousTransactionNotFoundException($previousTransactionId);
        }

        if ($previousTransaction->billerName() === QyssoBillerSettings::QYSSO
            && !$previousTransaction->status()->approved()
        ) {
            throw new PreviousTransactionShouldBeApprovedException($previousTransactionId);
        }

        if ($previousTransaction->billerName() !== QyssoBillerSettings::QYSSO) {
            throw new InvalidBillerNameException(QyssoBillerSettings::QYSSO, $previousTransaction->billerName());
        }

        if ($previousTransaction->chargeInformation() instanceof ChargeInformation
            && $previousTransaction->chargeInformation()->rebill() === null
        ) {
            throw new RebillNotSetException($previousTransactionId);
        }
    }
}
