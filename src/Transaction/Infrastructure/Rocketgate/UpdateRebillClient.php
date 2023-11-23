<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Rocketgate;

use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\StartRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\StopRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;

class UpdateRebillClient
{
    /**
     * @var StartRebillCommandHandler
     */
    private $startRebillHandler;

    /**
     * @var StopRebillCommandHandler
     */
    private $stopRebillHandler;

    /**
     * @var UpdateRebillCommandHandler
     */
    private $updateRebillHandler;

    /**
     * Update Rebill Client constructor.
     * @param StartRebillCommandHandler|null  $startRebillHandler  $startRebillHandler
     * @param StopRebillCommandHandler|null   $stopRebillHandler   $stopRebillHandler
     * @param UpdateRebillCommandHandler|null $updateRebillHandler $updateRebillHandler
     */
    public function __construct(
        ?StartRebillCommandHandler $startRebillHandler = null,
        ?StopRebillCommandHandler $stopRebillHandler = null,
        ?UpdateRebillCommandHandler $updateRebillHandler = null
    ) {
        $this->startRebillHandler  = $startRebillHandler;
        $this->stopRebillHandler   = $stopRebillHandler;
        $this->updateRebillHandler = $updateRebillHandler;
    }

    /**
     * @param UpdateRebillCommand $rocketgateUpdateRebillCommand Rocketgate Update Rebill Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function start(UpdateRebillCommand $rocketgateUpdateRebillCommand): string
    {
        Log::info('Send Rocketgate start rebill request');

        try {
            $jsonResponse = $this->startRebillHandler->execute($rocketgateUpdateRebillCommand);
        } catch (\Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param UpdateRebillCommand $rocketgateUpdateRebillCommand Rocketgate Update Rebill Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function stop(UpdateRebillCommand $rocketgateUpdateRebillCommand): string
    {
        Log::info('Send Rocketgate stop rebill request');

        try {
            $jsonResponse = $this->stopRebillHandler->execute($rocketgateUpdateRebillCommand);
        } catch (\Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }

    /**
     * @param UpdateRebillCommand $rocketgateUpdateRebillCommand Rocketgate Update Rebill Command
     * @return string
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(UpdateRebillCommand $rocketgateUpdateRebillCommand): string
    {
        Log::info('Send Rocketgate update rebill request');

        try {
            $jsonResponse = $this->updateRebillHandler->execute($rocketgateUpdateRebillCommand);
        } catch (\Exception $e) {
            throw new RocketgateServiceException($e);
        }

        return $jsonResponse;
    }
}
