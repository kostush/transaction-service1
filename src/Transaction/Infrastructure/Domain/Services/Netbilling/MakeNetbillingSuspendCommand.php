<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use Exception;
use DateTimeImmutable;
use ProBillerNG\Logger\Log;
use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ChargeAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;

class MakeNetbillingSuspendCommand extends ExternalCommand
{
    /**
     * @var BaseNetbillingCancelRebillAdapter
     */
    private $adapter;

    private $cancelRebillCommand;

    private $requestDate;

    /**
     * MakeNetbillingSuspendCommand constructor.suspend
     *
     * @param ChargeAdapterInterface $adapter
     * @param CancelRebillCommand    $cancelRebillCommand
     * @param DateTimeImmutable     $requestDate
     */
    public function __construct(
        ChargeAdapterInterface $adapter,
        CancelRebillCommand $cancelRebillCommand,
        DateTimeImmutable $requestDate
    ) {
        $this->adapter             = $adapter;
        $this->cancelRebillCommand = $cancelRebillCommand;
        $this->requestDate         = $requestDate;
    }

    /**
     * The code to be executed
     *
     * @return mixed
     */
    protected function run()
    {
        return $this->adapter->cancel($this->cancelRebillCommand, $this->requestDate);
    }

    /**
     * @return NetbillingBillerResponse
     * @throws Exception
     */
    protected function getFallback()
    {
        Log::info('Netbilling service error. Aborting transaction');

        // Return a abort transaction response
        return NetbillingBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}