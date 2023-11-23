<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;

class NetbillingUpdateRebillTranslatingService implements UpdateRebillService
{

    /** @var NetbillingUpdateRebillTranslatorFactory */
    protected $updateRebillTranslatorFactory;

    /**
     * NetbillingUpdateRebillTranslatingService constructor.
     * @param NetbillingUpdateRebillTranslatorFactory $netbillingUpdateRebillTranslatorFactory translator factory
     */
    public function __construct(NetbillingUpdateRebillTranslatorFactory $netbillingUpdateRebillTranslatorFactory)
    {
        $this->updateRebillTranslatorFactory = $netbillingUpdateRebillTranslatorFactory;
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     */
    public function start(RebillUpdateTransaction $transaction): BillerResponse
    {
        // TODO: Implement start() method.
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     */
    public function stop(RebillUpdateTransaction $transaction): BillerResponse
    {
        // TODO: Implement stop() method.
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     * @throws LoggerException
     */
    public function update(RebillUpdateTransaction $transaction): BillerResponse
    {
        $updateRebillTranslationService = $this->updateRebillTranslatorFactory->createUpdateRebillTranslator(
            $transaction->paymentInformation()
        );
        return $updateRebillTranslationService->update($transaction);
    }
}