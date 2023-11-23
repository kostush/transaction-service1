<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Domain\Services\LegacyService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;

class LegacyNewSaleTranslatingService implements LegacyService
{
    /**
     * @var LegacyNewSaleAdapter
     */
    private $adapter;

    /**
     * LegacyNewSaleTranslatingService constructor.
     * @param LegacyNewSaleAdapter $adapter Adapter
     */
    public function __construct(LegacyNewSaleAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param Member            $member      Member Member
     * @param ChargesCollection $charges     Charges
     * @return LegacyNewSaleBillerResponse
     */
    public function chargeNewSale(
        ChargeTransaction $transaction,
        ?Member $member,
        ChargesCollection $charges
    ): LegacyNewSaleBillerResponse {
        return $this->adapter->newSale($transaction, $charges, $member);
    }
}
