<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;

class CircuitBreakerNetbillingCancelRebillAdapterInterface extends CircuitBreaker implements BaseNetbillingCancelRebillAdapter
{
    private $adapter;

    /**
     * CircuitBreakerNetbillingSuspendRebillAdapterInterface constructor.
     *
     * @param CommandFactory                $commandFactory
     * @param NetbillingCancelRebillAdapter $cancelRebillAdapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        NetbillingCancelRebillAdapter $cancelRebillAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $cancelRebillAdapter;
    }

    /**
     * @param CancelRebillCommand $cancelRebillCommand
     * @param DateTimeImmutable   $requestDate
     *
     * @return NetbillingBillerResponse
     * @throws Exception
     */
    public function cancel(
        CancelRebillCommand $cancelRebillCommand,
        DateTimeImmutable $requestDate
    ): NetbillingBillerResponse {
        Log::info('Send Netbilling suspend rebill request');

        $command = $this->commandFactory->getCommand(
            MakeNetbillingSuspendCommand::class,
            $this->adapter,
            $cancelRebillCommand,
            $requestDate
        );
        return $command->execute();
    }
}