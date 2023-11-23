<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\BaseCommand as RocketgateChargeCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\NewCreditCardChargeAdapter;

class CircuitBreakerRocketgateChargeNewCreditCardAdapterInterface extends CircuitBreaker implements NewCreditCardChargeAdapter
{
    /**
     * @var RocketgateNewCreditCardChargeAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory                       $commandFactory       Command Factory
     * @param RocketgateNewCreditCardChargeAdapter $newCreditCardAdapter The new credit card adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateNewCreditCardChargeAdapter $newCreditCardAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $newCreditCardAdapter;
    }

    /**
     * Execute Rocketgate charge
     * @param ChargeWithNewCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     * @param \DateTimeImmutable             $requestDate             Request date
     * @throws \ProBillerNG\Logger\Exception
     * @return RocketgateCreditCardBillerResponse
     */
    public function charge(
        ChargeWithNewCreditCardCommand $rocketgateChargeCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {

        Log::info('Send Rocketgate charge request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateChargeCommand::class,
            $this->adapter,
            $rocketgateChargeCommand,
            $requestDate
        );
        return $command->execute();
    }
}
