<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;

class LegacyNewSaleCommand extends ExternalCommand
{
    /**
     * @var LegacyNewSaleAdapter
     */
    private $adapter;
    /**
     * @var ChargesCollection
     */
    private $charges;
    /**
     * @var ChargeTransaction
     */
    private $transaction;
    /**
     * @var Member|null
     */
    private $member;

    /**
     * @param LegacyNewSaleAdapter $adapter     Adapter
     * @param ChargeTransaction    $transaction Transaction
     * @param ChargesCollection    $charges     Charges
     * @param Member               $member      Member
     */
    public function __construct(
        LegacyNewSaleAdapter $adapter,
        ChargeTransaction $transaction,
        ChargesCollection $charges,
        ?Member $member
    ) {
        $this->adapter     = $adapter;
        $this->member      = $member;
        $this->transaction = $transaction;
        $this->charges     = $charges;
    }

    /**
     * Execute the command
     * @return LegacyNewSaleBillerResponse
     */
    protected function run()
    {
        return $this->adapter->newSale(
            $this->transaction,
            $this->charges,
            $this->member
        );
    }

    /**
     * Fallback for failure
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    protected function getFallback()
    {
        Log::info('Legacy service error. Aborting transaction');

        // Return a abort transaction response
        return LegacyNewSaleBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
