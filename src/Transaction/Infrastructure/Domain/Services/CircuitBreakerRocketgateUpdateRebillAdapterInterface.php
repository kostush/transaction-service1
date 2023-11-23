<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\UpdateRebillAdapter;

class CircuitBreakerRocketgateUpdateRebillAdapterInterface extends CircuitBreaker implements UpdateRebillAdapter
{
    /**
     * @var RocketgateSuspendRebillAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory                $commandFactory      Command Factory
     * @param RocketgateUpdateRebillAdapter $updateRebillAdapter The update rebill adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateUpdateRebillAdapter $updateRebillAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $updateRebillAdapter;
    }

    /**
     * @param UpdateRebillCommand $updateRebilCommand Update Rebill Command
     * @param \DateTimeImmutable  $requestDate        The request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function start(
        UpdateRebillCommand $updateRebilCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Send Rocketgate update rebill request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateStartUpdateRebillCommand::class,
            $this->adapter,
            $updateRebilCommand,
            $requestDate
        );

        return $command->execute();
    }

    /**
     * @param UpdateRebillCommand $updateRebilCommand Update Rebill Command
     * @param \DateTimeImmutable  $requestDate        The request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function stop(
        UpdateRebillCommand $updateRebilCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Send Rocketgate update rebill request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateStopUpdateRebillCommand::class,
            $this->adapter,
            $updateRebilCommand,
            $requestDate
        );

        return $command->execute();
    }

    /**
     * @param UpdateRebillCommand $updateRebilCommand Update Rebill Command
     * @param \DateTimeImmutable  $requestDate        The request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(
        UpdateRebillCommand $updateRebilCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Send Rocketgate update rebill request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateUpdateRebillCommand::class,
            $this->adapter,
            $updateRebilCommand,
            $requestDate
        );

        return $command->execute();
    }
}
