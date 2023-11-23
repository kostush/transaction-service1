<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\SimplifiedCompleteThreeDAdapter;

class CircuitBreakerRocketgateSimplifiedCompleteThreeDAdapter extends CircuitBreaker implements SimplifiedCompleteThreeDAdapter
{
    /**
     * @var RocketgateSimplifiedCompleteThreeDAdapter
     */
    private $adapter;

    /**
     * CircuitBreakerRocketgateSimplifiedCompleteThreeDAdapter constructor.
     * @param CommandFactory                            $commandFactory Command Factory
     * @param RocketgateSimplifiedCompleteThreeDAdapter $adapter        Simplified Complete ThreeD Adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateSimplifiedCompleteThreeDAdapter $adapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $adapter;
    }

    /**
     * @param SimplifiedCompleteThreeDCommand $simplifiedCompleteThreeDCommand Simplified Complete ThreeD Command
     * @param DateTimeImmutable               $requestDate                     Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function simplifiedComplete(
        SimplifiedCompleteThreeDCommand $simplifiedCompleteThreeDCommand,
        DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {
        Log::info('Send Rocketgate simplified complete threeD request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateSimplifiedCompleteThreeDCommand::class,
            $this->adapter,
            $simplifiedCompleteThreeDCommand,
            $requestDate
        );

        return $command->execute();
    }
}
