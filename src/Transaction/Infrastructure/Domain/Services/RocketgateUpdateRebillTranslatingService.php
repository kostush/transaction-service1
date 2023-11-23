<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;

class RocketgateUpdateRebillTranslatingService implements UpdateRebillService
{
    /**
     * @var RocketgateUpdateRebillTranslator
     */
    protected $rocketgateUpdateRebillTranslationService;

    /**
     * RocketgateUpdateRebillService constructor.
     * @param RocketgateUpdateRebillTranslator $rocketgateUpdateRebillTranslationService Translation Service
     */
    public function __construct(RocketgateUpdateRebillTranslator $rocketgateUpdateRebillTranslationService)
    {
        $this->rocketgateUpdateRebillTranslationService = $rocketgateUpdateRebillTranslationService;
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function start(RebillUpdateTransaction $transaction): BillerResponse
    {
        return $this->rocketgateUpdateRebillTranslationService->start($transaction);
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function stop(RebillUpdateTransaction $transaction): BillerResponse
    {
        return $this->rocketgateUpdateRebillTranslationService->stop($transaction);
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(RebillUpdateTransaction $transaction): BillerResponse
    {
        return $this->rocketgateUpdateRebillTranslationService->update($transaction);
    }
}
