<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionShouldBeApprovedException;
use ProBillerNG\Transaction\Domain\Model\Exception\RebillNotSetException;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;

class AddBillerInteractionForRebillOnPumapayCommandHandler extends BaseCommandHandler
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
     * AddBillerInteractionForRebillOnPumapayCommandHandler constructor.
     * @param TransactionRepository                        $repository     Repository
     * @param PumapayRebillPostbackTransactionDTOAssembler $dtoAssembler   DTO assembler
     * @param PumapayService                               $pumapayService Pumapay service
     * @param BILoggerService                              $biService      BI service
     */
    public function __construct(
        TransactionRepository $repository,
        PumapayRebillPostbackTransactionDTOAssembler $dtoAssembler,
        PumapayService $pumapayService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService      = $biService;
        $this->pumapayService = $pumapayService;
    }

    /**
     * @param Command $command Command
     * @return PumapayRebillPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): PumapayRebillPostbackCommandHttpDTO
    {
        try {
            if (!$command instanceof AddBillerInteractionForRebillOnPumapayCommand) {
                throw new InvalidCommandException(AddBillerInteractionForRebillOnPumapayCommand::class, $command);
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->previousTransactionId())
            );

            $this->validatePreviousTransactionData($previousTransaction, $command->previousTransactionId());

            // create a pending rebill transaction
            $rebillTransaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);

            $billerResponse = $this->pumapayService->translatePostback(
                json_encode($command->payload()),
                PostbackResponse::CHARGE_TYPE_REBILL
            );

            $rebillTransaction->updatePumapayTransactionFromBillerResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($rebillTransaction);

            $this->biService->write(
                new RebillUpdateTransactionCreated(
                    $rebillTransaction,
                    $billerResponse,
                    PumaPayBillerSettings::PUMAPAY,
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
     * @throws InvalidBillerNameException
     * @throws PreviousTransactionNotFoundException
     * @throws PreviousTransactionShouldBeApprovedException
     * @throws RebillNotSetException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function validatePreviousTransactionData(
        ?Transaction $previousTransaction,
        string $previousTransactionId
    ): void {
        if (!$previousTransaction instanceof Transaction) {
            throw new PreviousTransactionNotFoundException($previousTransactionId);
        }

        if ($previousTransaction->billerName() === PumaPayBillerSettings::PUMAPAY
            && !$previousTransaction->status()->approved()
        ) {
            throw new PreviousTransactionShouldBeApprovedException($previousTransactionId);
        }

        if ($previousTransaction->billerName() !== PumaPayBillerSettings::PUMAPAY) {
            throw new InvalidBillerNameException(PumaPayBillerSettings::PUMAPAY, $previousTransaction->billerName());
        }

        if ($previousTransaction->chargeInformation() instanceof ChargeInformation
            && $previousTransaction->chargeInformation()->rebill() === null
        ) {
            throw new RebillNotSetException($previousTransactionId);
        }
    }
}
