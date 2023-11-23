<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Logger\Log;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use Odesk\Phystrix\CommandFactory;

class CircuitBreakerNetbillingUpdateRebillAdapterInterface extends CircuitBreaker implements UpdateRebillNetbillingAdapter
{
    /**
     * @var RocketgateSuspendRebillAdapter
     */
    private $adapter;

    /**
     * CircuitBreakerNetbillingUpdateRebillAdapterInterface constructor.
     * @param CommandFactory                $commandFactory
     * @param NetbillingUpdateRebillAdapter $updateRebillAdapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        NetbillingUpdateRebillAdapter $updateRebillAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $updateRebillAdapter;
    }

    /**
     * @param UpdateRebillCommand $updateRebilCommand
     * @param \DateTimeImmutable  $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(
        UpdateRebillCommand $updateRebilCommand,
        \DateTimeImmutable $requestDate
    ) {
        Log::info('Send Netbilling update rebill request');

        $command = $this->commandFactory->getCommand(
            MakeNetbillingUpdateRebillCommand::class,
            $this->adapter,
            $updateRebilCommand,
            $requestDate
        );

        return $command->execute();
    }
}