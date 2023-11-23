<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand as RocketgateSuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ChargeAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateSuspendCommand extends ExternalCommand
{
    /**
     * @var RocketgateSuspendRebillAdapter
     */
    private $adapter;

    /**
     * @var RocketgateSuspendRebillCommand
     */
    private $rocketgateSuspendRebillCommand;

    /**
     * @var \DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateChargeCommand constructor.
     * @param ChargeAdapterInterface         $adapter                        Adapter
     * @param RocketgateSuspendRebillCommand $rocketgateSuspendRebillCommand Rocketgate Suspend Rebill Request
     * @param \DateTimeImmutable             $requestDate                    Request date
     */
    public function __construct(
        ChargeAdapterInterface $adapter,
        RocketgateSuspendRebillCommand $rocketgateSuspendRebillCommand,
        \DateTimeImmutable $requestDate
    ) {
        $this->adapter                        = $adapter;
        $this->rocketgateSuspendRebillCommand = $rocketgateSuspendRebillCommand;
        $this->requestDate                    = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    protected function run()
    {
        return $this->adapter->suspend($this->rocketgateSuspendRebillCommand, $this->requestDate);
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
