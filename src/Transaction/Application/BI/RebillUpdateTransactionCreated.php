<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;

/**
 * Class RebillUpdateTransactionCreated
 * @package ProBillerNG\Transaction\Application\BI
 */
class RebillUpdateTransactionCreated extends BaseEvent
{
    public const TYPE             = 'Transaction_Created';
    public const LATEST_VERSION   = '6';
    public const TRANSACTION_TYPE = 'rebill_update';

    /**
     * RebillUpdateTransactionCreated constructor.
     *
     * @param RebillUpdateTransaction $transaction    Rebill Update Transaction object
     * @param BillerResponse|null     $billerResponse Biller response
     * @param string                  $billerName     Biller name
     * @param string|null             $action         Action
     *
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    public function __construct(
        RebillUpdateTransaction $transaction,
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
