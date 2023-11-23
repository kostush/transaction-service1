<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\PostbackAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;

class AddBillerInteractionForJoinOnPumapayCommandHandler extends BaseCommandHandler
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
     * AddBillerInteractionForJoinOnPumapayCommandHandler constructor.
     * @param TransactionRepository                      $repository     Repository
     * @param PumapayJoinPostbackTransactionDTOAssembler $dtoAssembler   DTO Assembler
     * @param PumapayService                             $pumapayService Pumapay Service
     * @param BILoggerService                            $biService      BI Service
     */
    public function __construct(
        TransactionRepository $repository,
        PumapayJoinPostbackTransactionDTOAssembler $dtoAssembler,
        PumapayService $pumapayService,
        BILoggerService $biService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->biService      = $biService;
        $this->pumapayService = $pumapayService;
    }

    /**
     * @param Command $command Command
     * @return PumapayJoinPostbackCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): PumapayJoinPostbackCommandHttpDTO
    {
        try {
            if (!$command instanceof AddBillerInteractionForJoinOnPumapayCommand) {
                throw new InvalidCommandException(AddBillerInteractionForJoinOnPumapayCommand::class, $command);
            }

            $transaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            $this->validateTransactionData($transaction, $command->transactionId());

            $billerResponse = $this->pumapayService->translatePostback(
                json_encode($command->payload()),
                PostbackResponse::CHARGE_TYPE_JOIN
            );

            $transaction->updatePumapayTransactionFromBillerResponse($billerResponse);

            $this->repository->update($transaction);

            $this->biService->write(
                new TransactionUpdated(
                    $transaction,
                    $billerResponse,
                    PumaPayBillerSettings::PUMAPAY,
                    BillerSettings::ACTION_POSTBACK
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
     * @return void
     * @throws InvalidBillerNameException
     * @throws PostbackAlreadyProcessedException
     * @throws TransactionNotFoundException
     * @throws \ProBillerNG\Logger\Exception *@throws InvalidBillerNameException
     */
    protected function validateTransactionData(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof Transaction) {
            throw new TransactionNotFoundException($transactionId);
        }

        if ($transaction->billerName() !== PumaPayBillerSettings::PUMAPAY) {
            throw new InvalidBillerNameException(PumaPayBillerSettings::PUMAPAY, $transaction->billerName());
        }

        if (!$transaction->status()->pending()) {
            throw new PostbackAlreadyProcessedException($transactionId);
        }
    }
}
