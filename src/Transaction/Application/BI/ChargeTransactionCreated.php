<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use Exception;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;

/**
 * Class ChargeTransactionCreated
 * @package ProBillerNG\Transaction\Application\BI
 */
class ChargeTransactionCreated extends BaseEvent
{
    public const TYPE             = 'Transaction_Created';
    public const LATEST_VERSION   = '7';
    public const TRANSACTION_TYPE = 'charge';

    /**
     * ChargeTransactionCreated constructor.
     *
     * @param ChargeTransaction   $transaction    Charge Transaction object
     * @param BillerResponse|null $billerResponse BillerResponse
     * @param string              $billerName     Name of the biller
     * @param string|null         $action         Action
     *
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws Exception
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
