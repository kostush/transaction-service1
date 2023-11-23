<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Epoch\Application\Services\NewSaleCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;

class EpochNewSaleCommand extends ExternalCommand
{
    /**
     * @var EpochNewSaleAdapter
     */
    private $adapter;

    /**
     * @var NewSaleCommand
     */
    private $epochNewSaleCommand;

    /**
     * @param EpochNewSaleAdapter $adapter             Adapter
     * @param NewSaleCommand      $epochNewSaleCommand Epoch New Sale Request
     */
    public function __construct(
        EpochNewSaleAdapter $adapter,
        NewSaleCommand $epochNewSaleCommand
    ) {
        $this->adapter             = $adapter;
        $this->epochNewSaleCommand = $epochNewSaleCommand;
    }

    /**
     * Execute the command
     * @return EpochNewSaleBillerResponse
     * @throws \Exception
     */
    protected function run()
    {
        return $this->adapter->newSale($this->epochNewSaleCommand);
    }

    /**
     * Fallback for failure
     * @return string
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    protected function getFallback()
    {
        Log::info('Epoch service error. Aborting transaction');

        // Return a abort transaction response
        return EpochNewSaleBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
