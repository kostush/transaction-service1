<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Legacy;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\LegacyService;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;

/**
 * Class PerformLegacyNewSaleCommandHandler
 * @package ProBillerNG\Transaction\Application\Services\Transaction\Legacy
 */
class PerformLegacyNewSaleCommandHandler extends BaseCommandHandler
{
    /**
     * @var LegacyService
     */
    private $chargeService;

    /**
     * @var BILoggerService
     */
    private $biLoggerService;

    /**
     * PerformLegacyNewSaleCommandHandler constructor.
     * @param LegacyNewSaleTransactionDTOAssembler $dtoAssembler    DTO
     * @param TransactionRepository                $repository      Repository
     * @param LegacyService                        $legacyService   Legacy Service
     * @param BILoggerService                      $biLoggerService BILogger Service.
     */
    public function __construct(
        LegacyNewSaleTransactionDTOAssembler $dtoAssembler,
        TransactionRepository $repository,
        LegacyService $legacyService,
        BILoggerService $biLoggerService
    ) {
        parent::__construct($repository, $dtoAssembler);
        $this->biLoggerService = $biLoggerService;
        $this->chargeService   = $legacyService;
    }

    /**
     * @param Command $command Command
     * @return mixed|void
     * @throws Exception
     * @throws \Exception
     */
    public function execute(Command $command)
    {
        try {

            /** @var  PerformLegacyNewSaleCommand $command */
            $this->assertIsValidCommand($command);

            $charges = ChargesCollection::createFromArray($command->charges());

            $transaction = $this->createLegacyTransactions($charges, $command);

            $transaction->addCustomFieldsToLegacyBillerSetting($charges->getMainPurchase()->productId());

            // Perform charge
            $billerResponse = $this->chargeService->chargeNewSale(
                $transaction,
                $command->member(),
                $charges
            );

            /** @var ChargeTransaction $transaction */
            $transaction->updateLegacyTransactionFromBillerResponse($billerResponse);

            // Persist transaction entity
            $this->repository->add($transaction);

            //Write BiLogger event
            $event = new ChargeTransactionCreated($transaction, $billerResponse, $command->billerName());
            $this->biLoggerService->write($event);

            return $this->dtoAssembler->assemble($transaction);
        } catch (InvalidPayloadException | \InvalidArgumentException $e) {
            Log::logException($e);
            throw $e;
        } catch (\Throwable $e) {
            throw new TransactionCreationException($e);
        }
    }

    /**
     * @param PerformLegacyNewSaleCommand $command Command
     * @throws InvalidCommandException
     * @throws Exception
     * @return void
     */
    private function assertIsValidCommand(PerformLegacyNewSaleCommand $command): void
    {
        if (!($command instanceof PerformLegacyNewSaleCommand)) {
            throw new InvalidCommandException(PerformLegacyNewSaleCommand::class, $command);
        }
    }

    /**
     * @param ChargesCollection           $charges Charges
     * @param PerformLegacyNewSaleCommand $command Command
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     */
    private function createLegacyTransactions(
        ChargesCollection $charges,
        PerformLegacyNewSaleCommand $command
    ): ChargeTransaction {
        $charge = $charges->getMainPurchase();

        /**
         * Legacy-service is not prepared to receive postbackurl on payload, however
         * it accepts custom parameter to easily configuration of legacy-custom params
         * so that we can add the postbackUrl at custom fields and it will work correctly
         */
        $others = $command->others() ?? [];
        $others = array_merge($others, ['postbackUrl' => $command->postbackUrl()]);

        return ChargeTransaction::createTransactionOnLegacy(
            $charge->siteId(),
            LegacyBillerChargeSettings::LEGACY,
            ChargeInformation::createChargeInformationFromCharge($charge),
            $command->paymentType(),
            LegacyBillerChargeSettings::create(
                $command->legacyMemberId(),
                $command->billerName(),
                $command->returnUrl(),
                $command->postbackUrl(),
                $others
            ),
            $command->paymentMethod(),
            (!empty($command->member()) ? $command->member()->userName() : null),
            (!empty($command->member()) ? $command->member()->password() : null)
        );
    }
}
