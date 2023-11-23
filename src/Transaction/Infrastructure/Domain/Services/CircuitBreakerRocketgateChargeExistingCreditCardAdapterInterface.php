<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class CircuitBreakerRocketgateChargeExistingCreditCardAdapterInterface extends CircuitBreaker implements ExistingCreditCardChargeAdapter
{
    /**
     * @var RocketgateExistingCreditCardChargeAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory                            $commandFactory            Command Factory
     * @param RocketgateExistingCreditCardChargeAdapter $existingCreditCardAdapter The existing credit card adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateExistingCreditCardChargeAdapter $existingCreditCardAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $existingCreditCardAdapter;
    }

    /**
     * Execute Rocketgate charge
     * @param ChargeWithExistingCreditCardCommand $rocketgateChargeCommand Rocketgate Charge Command
     * @param \DateTimeImmutable                  $requestDate             Request date
     * @throws \ProBillerNG\Logger\Exception
     * @return RocketgateCreditCardBillerResponse
     */
    public function charge(
        ChargeWithExistingCreditCardCommand $rocketgateChargeCommand,
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
