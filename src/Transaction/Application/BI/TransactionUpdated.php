<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

class TransactionUpdated extends BaseEvent
{
    public const TYPE             = 'Transaction_Updated';
    public const LATEST_VERSION   = '2';
    public const TRANSACTION_TYPE = 'charge';

    /**
     * TransactionUpdated constructor.
     *
     * @param ChargeTransaction   $transaction    Charge Transaction object
     * @param BillerResponse|null $billerResponse BillerResponse
     * @param string              $billerName     Name of the biller
     * @param string|null         $action         Action
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     */
    public function __construct(
        ChargeTransaction $transaction,
        ?BillerResponse $billerResponse,
        string $billerName,
        string $action = null
    ) {
        parent::__construct(self::TYPE);

        $biEventValue = $this->createBaseEventValue(
            self::LATEST_VERSION,
            $transaction,
            $billerResponse,
            $billerName,
            self::TRANSACTION_TYPE,
            $action
        );

        $this->setValue($biEventValue);
    }
}
