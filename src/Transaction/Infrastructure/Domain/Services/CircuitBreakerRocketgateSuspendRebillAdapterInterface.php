<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\SuspendRebillAdapter;

class CircuitBreakerRocketgateSuspendRebillAdapterInterface extends CircuitBreaker implements SuspendRebillAdapter
{
    /**
     * @var RocketgateSuspendRebillAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory                 $commandFactory       Command Factory
     * @param RocketgateSuspendRebillAdapter $suspendRebillAdapter The suspend rebill adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateSuspendRebillAdapter $suspendRebillAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $suspendRebillAdapter;
    }

    /**
     * @param SuspendRebillCommand $suspendRebilCommand Suspend Rebill Command
     * @param \DateTimeImmutable   $requestDate          The request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function suspend(
        SuspendRebillCommand $suspendRebilCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Send Rocketgate suspend rebill request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateSuspendCommand::class,
            $this->adapter,
            $suspendRebilCommand,
            $requestDate
        );

        return $command->execute();
    }
}
