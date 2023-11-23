<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;

class TransactionUpdatedTemp extends BaseEvent
{
    public const TYPE             = 'Transaction_Updated';
    public const LATEST_VERSION   = '2';
    public const TRANSACTION_TYPE = 'charge';

    /**
     * TransactionUpdatedTemp constructor.
     *
     * @param array $event
     */
    public function __construct(array $event)
    {
        parent::__construct(self::TYPE);

        $eventBody = json_decode($event['event_body'], true);

        $billerResponse = isset($eventBody['biller_interaction_payload'])
            ? json_decode($eventBody['biller_interaction_payload'], true) : [];

        $paymentType = $eventBody['payment_type'] ?? null;

        if ($paymentType) {
            $paymentType = Arr::get(PaymentType::biPaymentTypeMapping(), $eventBody['payment_type'], null);
        }

        $biEventValue = [
            'version'               => self::LATEST_VERSION,
            'timestamp'             => $event['occurred_on'],
            'sessionId'             => $event['sessionId'] ?? null,
            'transactionId'         => $event['aggregate_id'],
            'billerTransactionId'   => $billerResponse['guidNo'] ?? null,
            'billerResponseDate'    => $billerResponse['transactionTime'] ?? null,
            'biller'                => 'Rocketgate',
            'transactionState'      => 'Transaction' . ucfirst($eventBody['status']),
            'paymentType'           => $paymentType,
            'paymentMethod'         => null,
            'paymentTemplate'       => null,
            "freeSale"              => isset($billerResponse['approvedAmount']) && $billerResponse['approvedAmount'] == '1.02' ? 1 : 0,
            "binRouting"            => self::NO_BIN_ROUTING,
            "transactionType"       => $event['transaction_type'] ?? self::TRANSACTION_TYPE,
            "action"                => RocketGateBillerSettings::ACTION_UPDATE,
            "previousTransactionId" => $eventBody['previous_transaction_id'] ?? null,
            "requiredToUse3D"       => null,
            "transactionWith3D"     => true, /// since they were all pending at first
            "threedsVersion"        => null,
            "billerResponse"        => $billerResponse,
            "historicalDataFix"     => true // added for monitoring purposes
        ];

        $this->setValue($biEventValue);
    }
}
