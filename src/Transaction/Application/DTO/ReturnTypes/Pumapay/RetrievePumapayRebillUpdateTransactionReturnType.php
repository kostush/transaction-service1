<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use Doctrine\Common\Collections\ArrayCollection;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrievePumapayRebillUpdateTransactionReturnType extends RetrieveTransactionReturnType
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
     * @param string|null $paymentType         Payment Type.
     * @param array|null  $transactionPayload  Transaction payload.
     * @param string|null $siteId              Site Id.
     * @param string|null $billerTransactionId Biller TransactionId
     */
    private function __construct(
        string $billerId,
        string $billerName,
        array $transactionPayload,
        ?string $paymentType,
        ?string $currency,
        ?string $siteId,
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
     * @return RetrievePumapayRebillUpdateTransactionReturnType
     */
    public static function createFromEntity(Transaction $transaction): self
    {
        $billerInteractionFields = self::getBillerInteractionFields($transaction->billerInteractions());

        $transactionPayload = [
            'transactionId'         => (string) $transaction->transactionId(),
            'previousTransactionId' => (string) $transaction->previousTransaction()->transactionId(),
            'createdAt'             => $transaction->createdAt(),
            'updatedAt'             => $transaction->updatedAt(),
            'type'                  => $billerInteractionFields['type'],
            'status'                => (string) $transaction->status(),
        ];

        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            $transactionPayload,
            $transaction->paymentType(),
            $transaction->chargeInformation() ? $transaction->chargeInformation()->currency()->code() : null,
            $transaction->siteId() ? (string) $transaction->siteId() : null,
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
        $type                = 'rebill_update';

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
}
