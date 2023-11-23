<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use Doctrine\Common\Collections\ArrayCollection;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;

class RetrieveTransactionReturnType
{
    use TransactionRequestResponseInteractionTrait;

    /** @var string */
    protected $billerId;

    /** @var string */
    protected $billerName;

    /** @var string */
    protected $merchantId; //TODO this is a RG specific filed, it should be removed from this base class

    /** @var string */
    protected $merchantPassword; //TODO this is a RG specific filed, it should be removed from this base class

    /**
     * @var string
     * @deprecated
     */
    protected $invoiceId;

    /**
     * @var string
     * @deprecated
     */
    protected $customerId; //TODO this is a RG specific filed, it should be removed from this base class

    /** @var string */
    protected $cardHash; //TODO this is a RG specific filed, it should be removed from this base class

    /**
     * @var string
     * @deprecated
     */
    protected $billerTransactionId; //TODO this is a RG specific filed, it should be removed from this base class

    /** @var TransactionReturnType */
    protected $transaction;

    /** @var string $cardDescription */
    private $cardDescription; //TODO this is a RG/NB specific filed, it should be removed from this base class

    /**
     * TransactionPayload constructor.
     *
     * @param string                $billerId            BillerId
     * @param string                $billerName          BillerName
     * @param string                $merchantId          MerchantId
     * @param string                $merchantPassword    MerchantPassword
     * @param null|string           $invoiceId           InvoiceId
     * @param null|string           $customerId          CustomerId
     * @param string                $cardHash            CardHash
     * @param string                $transactionId       TransactionId
     * @param string                $currency            Currency.
     * @param string                $siteId              Site Id.
     * @param string                $paymentType         Payment Type.
     * @param string                $merchantAccount     Merchant Account.
     * @param MemberReturnType|null $member              Member.
     * @param TransactionReturnType $transaction         Transaction.
     * @param int|null              $cardExpirationYear  Card Expiration Year
     * @param int|null              $cardExpirationMonth Card Expiration Month
     * @param string|null           $cardDescription     Card description
     */
    private function __construct(
        string $billerId,
        string $billerName,
        string $merchantId,
        string $merchantPassword,
        ?string $invoiceId,
        ?string $customerId,
        ?string $cardHash,
        ?string $transactionId,
        string $currency,
        string $siteId,
        string $paymentType,
        string $merchantAccount,
        ?MemberReturnType $member,
        TransactionReturnType $transaction,
        ?int $cardExpirationYear,
        ?int $cardExpirationMonth,
        ?string $cardDescription
    ) {
        $this->billerId            = $billerId;
        $this->billerName          = $billerName;
        $this->merchantId          = $merchantId;
        $this->merchantPassword    = $merchantPassword;
        $this->invoiceId           = $invoiceId;
        $this->customerId          = $customerId;
        $this->cardHash            = $cardHash;
        $this->transactionId       = $transactionId;
        $this->billerTransactionId = $transactionId;
        $this->currency            = $currency;
        $this->siteId              = $siteId;
        $this->paymentType         = $paymentType;
        $this->merchantAccount     = $merchantAccount;
        $this->member              = $member;
        $this->transaction         = $transaction;
        $this->cardExpirationYear  = $cardExpirationYear;
        $this->cardExpirationMonth = $cardExpirationMonth;
        $this->cardDescription     = $cardDescription;
    }

    /**
     * @param BillerInteraction|ArrayCollection $billerInteractions Transaction
     * @return array
     */
    protected static function getBillerInteractionFields($billerInteractions): array
    {
        $cardHash            = null;
        $billerTransactionId = null;
        $cardDescription     = null;
        if ($billerInteractions->count()) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($billerInteractions as $billerInteraction) {
                if ($billerInteraction->type() == BillerInteraction::TYPE_RESPONSE) {
                    $payload = json_decode($billerInteraction->payload());
                    if (isset($payload->cardHash)) {
                        $cardHash = $payload->cardHash;
                    }
                    if (isset($payload->guidNo)) {
                        $billerTransactionId = $payload->guidNo;
                    }
                    if (isset($payload->cardDescription)) {
                        $cardDescription = $payload->cardDescription;
                    }
                    break;
                }
            }
        }
        return [
            'cardHash'            => $cardHash,
            'billerTransactionId' => $billerTransactionId,
            'cardDescription'     => $cardDescription
        ];
    }

    /**
     * @param Transaction $transaction Transaction
     * @return array
     */
    protected static function retrieveBillerResponse(Transaction $transaction): array
    {
        $response = [];

        /** @var BillerInteraction $billerInteraction */
        foreach ($transaction->billerInteractions() as $billerInteraction) {
            if ($billerInteraction->type() !== BillerInteraction::TYPE_RESPONSE) {
                continue;
            }

            $response = json_decode($billerInteraction->payload(), true);

            if (isset($response['reasonCode']) && $response['reasonCode'] == RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT) {
                return $response;
            }
        }

        return $response;
    }
}
