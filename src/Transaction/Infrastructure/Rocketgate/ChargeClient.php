<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Rocketgate;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;

class ChargeClient
{
    /**
     * @var ChargeWithNewCreditCardCommandHandler|null
     */
    private $chargeWithNewCreditCardHandler;

    /**
     * @var ChargeWithExistingCreditCardCommandHandler|null
     */
    private $chargeWithExistingCreditCardHandler;

    /**
     * @var SuspendRebillCommandHandler|null
     */
    private $suspendRebillHandler;

    /**
     * @var CompleteThreeDCreditCardCommandHandler|null
     */
    private $completeThreeDHandler;

    /**
     * @var SimplifiedCompleteThreeDCommandHandler|null
     */
    private $simplifiedCompleteThreeDHandler;

    /**
     * @var CardUploadCommandHandler|null
     */
    private $cardUploadHandler;

    /**
     * Charge Client constructor.
     * @param ChargeWithNewCreditCardCommandHandler|null      $newCreditCardHandler            New credit card handler
     * @param ChargeWithExistingCreditCardCommandHandler|null $existingCreditCardHandler       Existing credit card Handler
     * @param SuspendRebillCommandHandler|null                $suspendRebillHandler            Suspend Rebill Handler
     * @param CompleteThreeDCreditCardCommandHandler|null     $completeThreeDHandler           Complete ThreeD Handler
     * @param SimplifiedCompleteThreeDCommandHandler|null     $simplifiedCompleteThreeDHandler Simplified Complete ThreeD Handler
     * @param CardUploadCommandHandler|null                   $cardUploadCommandHandler        The card upload handler
     */
    public function __construct(
        ?ChargeWithNewCreditCardCommandHandler $newCreditCardHandler = null,
        ?ChargeWithExistingCreditCardCommandHandler $existingCreditCardHandler = null,
        ?SuspendRebillCommandHandler $suspendRebillHandler = null,
        ?CompleteThreeDCreditCardCommandHandler $completeThreeDHandler = null,
        ?SimplifiedCompleteThreeDCommandHandler $simplifiedCompleteThreeDHandler = null,
        ?CardUploadCommandHandler $cardUploadCommandHandler = null
    ) {
        $this->chargeWithNewCreditCardHandler      = $newCreditCardHandler;
        $this->chargeWithExistingCreditCardHandler = $existingCreditCardHandler;
        $this->suspendRebillHandler                = $suspendRebillHandler;
        $this->completeThreeDHandler               = $completeThreeDHandler;
        $this->simplifiedCompleteThreeDHandler     = $simplifiedCompleteThreeDHandler;
        $this->cardUploadHandler                   = $cardUploadCommandHandler;
    }

    /**
     * @param ChargeWithNewCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     *
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function chargeNewCreditCard(ChargeWithNewCreditCardCommand $rocketgateChargeCommand): string
    {
        Log::info('Send Rocketgate charge request');

        try {
            $jsonResponse = $this->chargeWithNewCreditCardHandler->execute($rocketgateChargeCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param ChargeWithExistingCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     *
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function chargeExistingCreditCard(ChargeWithExistingCreditCardCommand $rocketgateChargeCommand): string
    {
        Log::info('Send Rocketgate charge request');

        try {
            $jsonResponse = $this->chargeWithExistingCreditCardHandler->execute($rocketgateChargeCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param SuspendRebillCommand $rocketgateSuspendRebillCommand Rocketgate Suspend Rebill Command
     *
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function suspendRebill(SuspendRebillCommand $rocketgateSuspendRebillCommand): string
    {
        Log::info('Send Rocketgate suspend rebill request');

        try {
            $jsonResponse = $this->suspendRebillHandler->execute($rocketgateSuspendRebillCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param CompleteThreeDCreditCardCommand $rocketgateCompleteThreeDCommand Rocketgate Complete ThreeD Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function completeThreeD(CompleteThreeDCreditCardCommand $rocketgateCompleteThreeDCommand): string
    {
        Log::info('Send Rocketgate complete threeD request');

        try {
            $jsonResponse = $this->completeThreeDHandler->execute($rocketgateCompleteThreeDCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param SimplifiedCompleteThreeDCommand $command Rocketgate Simplified Complete ThreeD Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function simplifiedCompleteThreeD(SimplifiedCompleteThreeDCommand $command): string
    {
        Log::info('Send Rocketgate simplified complete threeD request');

        try {
            $jsonResponse = $this->simplifiedCompleteThreeDHandler->execute($command);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param CardUploadCommand $rocketgateCardUploadCommand Rocketgate Complete ThreeD Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function cardUpload(CardUploadCommand $rocketgateCardUploadCommand): string
    {
        Log::info('Send Rocketgate card upload request');

        try {
            $jsonResponse = $this->cardUploadHandler->execute($rocketgateCardUploadCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }
}
