<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Rocketgate;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCheckCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCheckCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;

/**
 * Class OtherPaymentTypeChargeClient
 * @package ProBillerNG\Transaction\Infrastructure\Rocketgate
 */
class OtherPaymentTypeChargeClient
{
    /**
     * @var ChargeWithNewCheckCommandHandler|null
     */
    private $chargeWithNewCheckCommandHandler;

    /**
     * OtherPaymentTypeChargeClient constructor.
     *
     * @param ChargeWithNewCheckCommandHandler $chargeWithNewCheckCommandHandler
     */
    public function __construct(ChargeWithNewCheckCommandHandler $chargeWithNewCheckCommandHandler)
    {
        $this->chargeWithNewCheckCommandHandler = $chargeWithNewCheckCommandHandler;
    }

    /**
     * @param ChargeWithNewCheckCommand $chargeWithNewCheckCommand
     *
     * @return string
     * @throws RocketgateServiceException
     * @throws LoggerException
     */
    public function chargeOtherPaymentType(
        ChargeWithNewCheckCommand $chargeWithNewCheckCommand
    ): string {
        Log::info('Send Rocketgate check purchase request');

        try {
            $jsonResponse = $this->chargeWithNewCheckCommandHandler->execute($chargeWithNewCheckCommand);
        } catch (Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }
}
