<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use Exception;
use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;

class MakeNetbillingUpdateRebillCommand extends ExternalCommand
{
    /**
     * @var NetbillingUpdateRebillAdapter
     */
    private $adapter;

    /**
     * @var UpdateRebillCommand
     */
    private $netbillingUpdateRebillCommand;

    /**
     * @var DateTimeImmutable
     */
    private $requestDate;

    public function __construct(
        UpdateRebillNetbillingAdapter $adapter,
        UpdateRebillCommand $netbillingUpdateRebillCommand,
        DateTimeImmutable $requestDate
    ) {
        $this->adapter                       = $adapter;
        $this->netbillingUpdateRebillCommand = $netbillingUpdateRebillCommand;
        $this->requestDate                   = $requestDate;
    }

    /**
     * Execute the command
     * @return NetbillingBillerResponse
     * @throws Exception
     */
    protected function run()
    {
        return $this->adapter->update($this->netbillingUpdateRebillCommand, $this->requestDate);
    }

    /**
     * Fallback for failure
     * @return string
     * @throws LoggerException
     * @throws Exception
     */
    protected function getFallback()
    {
        Log::info('Netbilling service error. Aborting transaction');

        // Return a abort transaction response
        return NetbillingBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}