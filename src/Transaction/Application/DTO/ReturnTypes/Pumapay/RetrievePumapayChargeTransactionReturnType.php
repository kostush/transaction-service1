<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use Doctrine\Common\Collections\ArrayCollection;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrievePumapayChargeTransactionReturnType extends RetrieveTransactionReturnType
{
    /** @var string */
    private $currency;

    /** @var string */
    private $siteId;

    /** @var string */
    private $paymentType;

    /**
     * TransactionPayload constructor.
     *
     * @param string      $billerId            BillerId
     * @param string      $billerName          BillerName
     * @param string      $currency            Currency.
     * @param string      $siteId              Site Id.
     * @param string      $paymentType         Payment Type.
     * @param array       $transactionPayload  Transaction payload.
     * @param string|null $billerTransactionId Biller TransactionId
     */
    private function __construct(
        string $billerId,
        string $billerName,
        string $currency,
        string $siteId,
        string $paymentType,
        array $transactionPayload,
        ?string $billerTransactionId
    ) {
        $this->billerId            = $billerId;
        $this->billerName          = $billerName;
        $this->billerTransactionId = $billerTransactionId;
        $this->currency            = $currency;
        $this->siteId              = $siteId;
        $this->paymentType         = $paymentType;
        $this->transaction         = $transactionPayload;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RetrievePumapayChargeTransactionReturnType
     */
    public static function createFromEntity(Transaction $transaction): RetrievePumapayChargeTransactionReturnType
    {
        $memberPayload = null;

        $transactionPayload = self::createFromTransaction($transaction);

        $billerInteractionFields      = self::getBillerInteractionFields($transaction->billerInteractions());
        $transactionPayload['type']   = $billerInteractionFields['type'];
        $transactionPayload['status'] = (string) $transaction->status();

        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            $transaction->chargeInformation()->currency()->code(),
            (string) $transaction->siteId(),
            $transaction->paymentType(),
            $transactionPayload,
            $billerInteractionFields['billerTransactionId']
        );
    }

    /**
     * @param BillerInteraction|ArrayCollection $billerInteractions Transaction
     * @return array
     */
    protected static function getBillerInteractionFields($billerInteractions): array
    {
        $billerTransactionId = null;
        $type                = 'join';

        if ($billerInteractions->count()) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($billerInteractions as $billerInteraction) {
                if ($billerInteraction->isResponseType()) {
                    $payload = json_decode($billerInteraction->payload(), true);

                    if (isset($payload['response']['transactionData']['id'])) {
                        $billerTransactionId = $payload['response']['transactionData']['id'];
                        $type                = $payload['type'];
                        break;
                    }
                }
            }
        }

        return [
            'billerTransactionId' => $billerTransactionId,
            'type'                => $type
        ];
    }

    /**
     * @param ChargeTransaction $transaction The Transaction object
     * @return array
     */
    protected static function createFromTransaction(ChargeTransaction $transaction): array
    {
        $rebillValue = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->amount()->value() : null;

        $rebillFrequency = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->frequency() : null;

        $rebillStart = ($transaction->chargeInformation()->rebill() !== null) ?
            (string) $transaction->chargeInformation()->rebill()->start() : null;

        return [
            'transactionId'   => (string) $transaction->transactionId(),
            'amount'          => (string) $transaction->chargeInformation()->amount()->value(),
            'createdAt'       => $transaction->createdAt(),
            'updatedAt'       => $transaction->updatedAt(),
            'rebillValue'     => $rebillValue,
            'rebillFrequency' => $rebillFrequency,
            'rebillStart'     => $rebillStart
        ];
    }
}
