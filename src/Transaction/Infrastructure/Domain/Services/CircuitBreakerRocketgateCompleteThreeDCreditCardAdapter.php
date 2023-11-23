<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class CircuitBreakerRocketgateCompleteThreeDCreditCardAdapter extends CircuitBreaker implements CompleteThreeDAdapter
{
    /**
     * @var RocketgateCompleteThreeDCreditCardAdapter
     */
    private $adapter;

    /**
     * CircuitBreakerRocketgateCompleteThreeDCreditCardAdapter constructor.
     * @param CommandFactory                            $commandFactory Command Factory
     * @param RocketgateCompleteThreeDCreditCardAdapter $adapter        Complete ThreeD Adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateCompleteThreeDCreditCardAdapter $adapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $adapter;
    }

    /**
     * @param CompleteThreeDCreditCardCommand $completeThreeDCommand Complete ThreeD Command
     * @param DateTimeImmutable               $requestDate           Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function complete(
        CompleteThreeDCreditCardCommand $completeThreeDCommand,
        DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {

        Log::info('Send Rocketgate complete threeD request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateCompleteThreeDCommand::class,
            $this->adapter,
            $completeThreeDCommand,
            $requestDate
        );

        return $command->execute();
    }
}
