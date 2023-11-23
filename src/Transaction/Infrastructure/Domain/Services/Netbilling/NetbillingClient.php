<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Netbilling\Application\Exception\NetbillingApplicationException;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommandHandler;
use ProBillerNG\Netbilling\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Netbilling\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommandHandler;
use ProBillerNG\Netbilling\Domain\Model\Exception\NetbillingException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use Throwable;

class NetbillingClient
{
    /**
     * @var ChargeWithNewCreditCardCommandHandler
     */
    private $chargeWithNewCreditCardHandler;

    /**
     * @var ChargeWithExistingCreditCardCommandHandler
     */
    private $chargeWithExistingCardHandler;

    /**
     * @var UpdateRebillCommandHandler
     */
    private $updateRebillCommandHandler;

    /**
     * @var CancelRebillCommandHandler
     */
    private $cancelRebillCommandHandler;

    /**
     * NetbillingClient constructor.
     * @param ChargeWithNewCreditCardCommandHandler|null $newCreditCardHandler             new card handler
     * @param ChargeWithExistingCreditCardCommandHandler $existingCreditCardCommandHandler existing card handler
     * @param UpdateRebillCommandHandler                 $updateRebillCommandHandler
     * @param CancelRebillCommandHandler                 $cancelRebillCommandHandler
     */
    public function __construct(
        ChargeWithNewCreditCardCommandHandler $newCreditCardHandler,
        ChargeWithExistingCreditCardCommandHandler $existingCreditCardCommandHandler,
        UpdateRebillCommandHandler $updateRebillCommandHandler,
        CancelRebillCommandHandler $cancelRebillCommandHandler
    ) {
        $this->chargeWithNewCreditCardHandler = $newCreditCardHandler;
        $this->chargeWithExistingCardHandler  = $existingCreditCardCommandHandler;
        $this->updateRebillCommandHandler     = $updateRebillCommandHandler;
        $this->cancelRebillCommandHandler     = $cancelRebillCommandHandler;
    }

    /**
     * @param CreditCardChargeCommand $netbillingCreditCardChargeCommand new credit charge command
     *
     * @return string
     * @throws NetbillingServiceException
     * @throws LoggerException|Throwable
     */
    public function chargeNewCreditCard(CreditCardChargeCommand $netbillingCreditCardChargeCommand): string
    {
        Log::info('Send Netbilling new card charge request');

        try {
            $jsonResponse = $this->chargeWithNewCreditCardHandler->execute($netbillingCreditCardChargeCommand);
        } catch (NetbillingException | NetbillingApplicationException | Exception $e) {
            throw new NetbillingServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param CreditCardChargeCommand $netbillingCreditCardChargeCommand Charge Command
     * @return string
     * @throws NetbillingServiceException
     * @throws LoggerException
     */
    public function chargeExistingCreditCard(CreditCardChargeCommand $netbillingCreditCardChargeCommand): string
    {
        Log::info('Send Netbilling existing card charge request');

        try {
            $jsonResponse = $this->chargeWithExistingCardHandler->execute($netbillingCreditCardChargeCommand);
        } catch (NetbillingException | NetbillingApplicationException | Exception $e) {
            throw new NetbillingServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param UpdateRebillCommand $updateRebillCommand
     *
     * @return string
     * @throws LoggerException
     * @throws NetbillingServiceException|Throwable
     */
    public function updateRebill(UpdateRebillCommand $updateRebillCommand): string
    {
        Log::info('Send Netbilling update rebill request');

        try {
            $jsonResponse = $this->updateRebillCommandHandler->execute($updateRebillCommand);
        } catch (NetbillingException | NetbillingApplicationException | Exception $e) {
            throw new NetbillingServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param CancelRebillCommand $command
     *
     * @return mixed
     * @throws LoggerException
     * @throws NetbillingServiceException|Throwable
     */
    public function cancelRebill(CancelRebillCommand $command)
    {
        Log::info('Send Netbilling suspend request');

        try {
            $jsonResponse = $this->cancelRebillCommandHandler->execute($command);
        } catch (NetbillingException | NetbillingApplicationException | Exception $e) {
            throw new NetbillingServiceException($e);
        }

        return $jsonResponse;
    }
}
