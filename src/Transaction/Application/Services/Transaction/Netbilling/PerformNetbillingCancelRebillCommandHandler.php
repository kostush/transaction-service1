<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use InvalidArgumentException;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\CreateUpdateRebillTransactionTrait;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use Throwable;

class PerformNetbillingCancelRebillCommandHandler extends BaseCommandHandler
{
    use CreateUpdateRebillTransactionTrait;

    /**
     * @var ChargeService
     */
    private $chargeService;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;

    /**
     * PerformNetbillingCancelRebillCommandHandler constructor.
     * @param HttpCommandDTOAssembler $dtoAssembler
     * @param TransactionRepository   $repository
     * @param ChargeService           $chargeService
     * @param BILoggerService         $biLoggerService
     */
    public function __construct(
        HttpCommandDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        ChargeService $chargeService,
        BILoggerService $biLoggerService
    ) {
        parent::__construct($repository, $dtoAssembler);

        $this->chargeService   = $chargeService;
        $this->biLoggerService = $biLoggerService;
    }

    /**
     * Executes a command
     *
     * @param Command $command Command
     *
     * @return mixed
     * @throws InvalidPayloadException
     * @throws NetbillingServiceException
     * @throws TransactionCreationException
     * @throws TransactionNotFoundException
     * @throws Exception
     */
    public function execute(Command $command)
    {
        try {
            if (!$command instanceof PerformNetbillingCancelRebillCommand) {
                throw new InvalidCommandException(PerformNetbillingCancelRebillCommand::class, $command);
            }

            Log::info('Begin processing cancel rebill Netbilling transaction');

            if (empty($command->transactionId())) {
                throw new MissingTransactionInformationException('transactionId');
            }

            $previousTransaction = $this->repository->findById(
                (string) TransactionId::createFromString($command->transactionId())
            );

            if (!$previousTransaction instanceof Transaction) {
                throw new TransactionNotFoundException($command->transactionId());
            }

            if ($previousTransaction->billerName() !== NetbillingBillerSettings::NETBILLING) {
                throw new InvalidTransactionInformationException('A non-Netbilling transaction id was provided');
            }

            // Create entity
            $transaction = $this->createNetbillingCancelRebillTransaction($command, $previousTransaction);

            // Perform suspend
            $billerResponse = $this->chargeService->suspendRebill($transaction);

            $transaction->updateTransactionFromNetbillingResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new RebillUpdateTransactionCreated(
                $transaction,
                $billerResponse,
                NetbillingBillerSettings::NETBILLING,
                NetbillingBillerSettings::ACTION_CANCEL
            );

            $this->biLoggerService->write($event);

            // Return DTO
            return $this->dtoAssembler->assemble($transaction);

        } catch (InvalidPayloadException | TransactionNotFoundException | InvalidArgumentException | NetbillingServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }
}
