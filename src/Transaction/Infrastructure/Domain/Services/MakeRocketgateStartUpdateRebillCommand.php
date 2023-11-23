<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand as RocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateStartUpdateRebillCommand extends ExternalCommand
{
    /**
     * @var RocketgateUpdateRebillAdapter
     */
    private $adapter;

    /**
     * @var RocketgateUpdateRebillCommand
     */
    private $rocketgateUpdateRebillCommand;

    /**
     * @var \DateTimeImmutable
     */
    private $requestDate;

    /**
     * @param UpdateRebillAdapter           $adapter                       Adapter
     * @param RocketgateUpdateRebillCommand $rocketgateUpdateRebillCommand Rocketgate Suspend Rebill Request
     * @param \DateTimeImmutable            $requestDate                   Request date
     */
    public function __construct(
        UpdateRebillAdapter $adapter,
        RocketgateUpdateRebillCommand $rocketgateUpdateRebillCommand,
        \DateTimeImmutable $requestDate
    ) {
        $this->adapter                       = $adapter;
        $this->rocketgateUpdateRebillCommand = $rocketgateUpdateRebillCommand;
        $this->requestDate                   = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    protected function run()
    {
        return $this->adapter->start($this->rocketgateUpdateRebillCommand, $this->requestDate);
    }

    /**
     * Fallback for failure
     * @return string
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    protected function getFallback()
    {
        Log::info('Rocketgate service error. Aborting transaction');

        // Return a abort transaction response
        return RocketgateCreditCardBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
