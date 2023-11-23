<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\BaseCommand as RocketgateCreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ChargeAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateChargeCommand extends ExternalCommand
{
    /**
     * @var RocketgateNewCreditCardChargeAdapter
     */
    private $adapter;

    /**
     * @var ChargeAdapterInterface
     */
    private $rocketgateChargeCommand;

    /**
     * @var \DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateChargeCommand constructor.
     * @param ChargeAdapterInterface            $adapter                 Adapter
     * @param RocketgateCreditCardChargeCommand $rocketgateChargeCommand Rocketgate Charge Request
     * @param \DateTimeImmutable                $requestDate             Request date
     */
    public function __construct(
        ChargeAdapterInterface $adapter,
        RocketgateCreditCardChargeCommand $rocketgateChargeCommand,
        \DateTimeImmutable $requestDate
    ) {
        $this->adapter                 = $adapter;
        $this->rocketgateChargeCommand = $rocketgateChargeCommand;
        $this->requestDate             = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    protected function run()
    {
        return $this->adapter->charge($this->rocketgateChargeCommand, $this->requestDate);
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
