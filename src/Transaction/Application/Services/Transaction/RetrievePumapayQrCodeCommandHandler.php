<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayQrCodeHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Domain\Services\PumapayTransactionService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayRetrieveQrCodeBillerResponse;

/**
 * Class RetrievePumapayQrCodeCommandHandler
 * @package ProBillerNG\Transaction\Application\Services\Transaction
 */
class RetrievePumapayQrCodeCommandHandler extends BaseCommandHandler
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
     * @var PumapayTransactionService
     */
    private $pumapayTransactionService;

    /**
     * RetrievePumapayQrCodeCommandHandler constructor.
     * @param TransactionRepository                $repository                Repository
     * @param PumapayQrCodeHttpCommandDTOAssembler $dtoAssembler              DTO Assembler
     * @param PumapayService                       $pumapayService            QrCodeService
     * @param BILoggerService                      $biService                 BI service
     * @param PumapayTransactionService            $pumapayTransactionService Creates or update transaction
     */
    public function __construct(
        TransactionRepository $repository,
        PumapayQrCodeHttpCommandDTOAssembler $dtoAssembler,
        PumapayService $pumapayService,
        BILoggerService $biService,
        PumapayTransactionService $pumapayTransactionService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->pumapayService            = $pumapayService;
        $this->biService                 = $biService;
        $this->pumapayTransactionService = $pumapayTransactionService;
    }

    /**
     * @param Command $command Command
     * @return RetrieveQrCodeCommandHttpDTO
     * @throws \Exception
     */
    public function execute(Command $command): RetrieveQrCodeCommandHttpDTO
    {
        try {
            if (!$command instanceof RetrievePumapayQrCodeCommand) {
                throw new InvalidCommandException(RetrievePumapayQrCodeCommand::class, $command);
            }

            $transaction = $this->pumapayTransactionService->createOrUpdateTransaction($command);

            /** @var PumapayRetrieveQrCodeBillerResponse $billerResponse */
            $billerResponse = $this->pumapayService->retrieveQrCode($transaction);

            // Update transaction
            $transaction->updatePumapayTransactionFromBillerResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            $event = new ChargeTransactionCreated($transaction, $billerResponse, PumaPayBillerSettings::PUMAPAY);
            $this->biService->write($event);

            // Return DTO
            return $this->dtoAssembler->assemble(
                $transaction,
                $billerResponse->qrCode(),
                $billerResponse->encryptText()
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
