<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateUpdateRebillBillerFields;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebillUpdateSettings;

class RebillUpdateTransaction extends Transaction
{
    public const TYPE = 'rebillUpdate';

    /**
     * Transaction constructor.
     * @param TransactionId                    $transactionId        The TransactionId VO
     * @param string                           $billerName           The biller name
     * @param BillerSettings|null              $billerChargeSettings The biller charge settings VO
     * @param DateTimeImmutable                $createdAt            The created at time stamp
     * @param DateTimeImmutable                $updatedAt            The update at time stamp
     * @param AbstractStatus                   $status               The status VO
     * @param Transaction|null                 $previousTransaction  The previous transaction
     * @param PaymentInformation               $paymentInformation   The payment information VO
     * @param ChargeInformation                $chargeInformation    The charge information VO
     * @param string                           $paymentType          The payment type
     * @param BillerInteractionCollection|null $billerInteractions   The biller interactions collection
     * @throws Exception
     */
    private function __construct(
        TransactionId $transactionId,
        string $billerName,
        ?BillerSettings $billerChargeSettings,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        AbstractStatus $status,
        ?Transaction $previousTransaction,
        ?PaymentInformation $paymentInformation,
        ?ChargeInformation $chargeInformation,
        ?string $paymentType,
        ?BillerInteractionCollection $billerInteractions
    ) {
        $this->transactionId        = $transactionId;
        $this->billerName           = $billerName;
        $this->paymentInformation   = $paymentInformation;
        $this->chargeInformation    = $chargeInformation;
        $this->billerChargeSettings = $billerChargeSettings;
        $this->createdAt            = $createdAt;
        $this->updatedAt            = $updatedAt;
        $this->status               = $status;
        $this->billerInteractions   = $billerInteractions;
        $this->initPaymentType($paymentType);
        $this->previousTransaction   = $previousTransaction;
        $this->originalTransactionId = $previousTransaction->getEntityId();

        /** TODO kept for backwards compatibility - billerId removal */
        $this->billerId = $this->billerId();

        Log::info('New transaction entity created', ['transactionId' => (string) $transactionId]);
    }

    /**
     * @param string|null $paymentType Payment type (method)
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initPaymentType(?string $paymentType): void
    {
        if (empty($paymentType)) {
            //if no payment type is provided,
            // assume it's a rebill update operation without any charge
            return;
        }
        switch ($paymentType) {
            case PaymentType::BANKTRANSFER:
            case PaymentType::CREDIT_CARD:
                break;
            default:
                throw new InvalidChargeInformationException('method');
        }
        $this->paymentType = $paymentType;
    }

    /**
     * @param Transaction                        $previousTransaction The previous transaction
     * @param string                             $billerName          The biller name
     * @param RocketGateUpdateRebillBillerFields $billerFields        The biller fields
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createCancelRebillTransaction(
        Transaction $previousTransaction,
        string $billerName,
        RocketGateUpdateRebillBillerFields $billerFields
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            $billerName,
            RocketGateRebillUpdateSettings::create(
                $billerFields->merchantId(),
                $billerFields->merchantPassword(),
                $billerFields->merchantCustomerId(),
                $billerFields->merchantInvoiceId(),
                $billerFields->merchantAccount()
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            null,
            null,
            null,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction                        $previousTransaction Previous Transaction
     * @param string                             $billerName          Biller Name
     * @param RocketGateUpdateRebillBillerFields $billerFields        Biller Fields
     * @param PaymentInformation                 $paymentInformation  Payment Information
     * @param ChargeInformation                  $chargeInformation   Charge Information
     * @param string                             $paymentType         Payment Type
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createUpdateRebillTransaction(
        Transaction $previousTransaction,
        string $billerName,
        RocketGateUpdateRebillBillerFields $billerFields,
        PaymentInformation $paymentInformation,
        ChargeInformation $chargeInformation,
        string $paymentType
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            $billerName,
            RocketGateRebillUpdateSettings::create(
                $billerFields->merchantId(),
                $billerFields->merchantPassword(),
                $billerFields->merchantCustomerId(),
                $billerFields->merchantInvoiceId(),
                $billerFields->merchantAccount()
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            $paymentInformation,
            $chargeInformation,
            $paymentType,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction                    $previousTransaction  Previous Transaction
     * @param string                         $billerName           Biller Name
     * @param NetbillingRebillUpdateSettings $rebillUpdateSettings Rebill Update Settings
     * @param PaymentInformation             $paymentInformation   Payment Information
     * @param ChargeInformation              $chargeInformation    Charge Information
     * @param string                         $paymentType          Payment Type
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createNetbillingUpdateRebillTransaction(
        Transaction $previousTransaction,
        string $billerName,
        NetbillingRebillUpdateSettings $rebillUpdateSettings,
        PaymentInformation $paymentInformation,
        ChargeInformation $chargeInformation,
        string $paymentType
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            $billerName,
            $rebillUpdateSettings,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            $paymentInformation,
            $chargeInformation,
            $paymentType,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction $previousTransaction Previous transaction
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createPumapayRebillUpdateTransaction(
        Transaction $previousTransaction
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            PumaPayBillerSettings::PUMAPAY,
            null,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            null,
            null,
            null,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction $previousTransaction Previous transaction
     * @param string      $businessId          Business Id
     * @param string      $businessModel       Business model
     * @param string      $apiKey              Api Key
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createCancelRebillPumapayTransaction(
        Transaction $previousTransaction,
        string $businessId,
        string $businessModel,
        string $apiKey
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            PumaPayBillerSettings::PUMAPAY,
            PumapayRebillUpdateSettings::create(
                $businessId,
                $businessModel,
                $apiKey
            ),
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            null,
            null,
            null,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction                    $previousTransaction Previous transaction
     * @param string                         $billerName          Biller Name
     * @param NetbillingRebillUpdateSettings $billerFields        Biller fields
     * @return RebillUpdateTransaction
     *
     * @throws Exception
     */
    public static function createNetbillingCancelRebillTransaction(
        Transaction $previousTransaction,
        string $billerName,
        NetbillingRebillUpdateSettings $billerFields
    ): RebillUpdateTransaction {
        return new static(
            TransactionId::create(),
            $billerName,
            $billerFields,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            null,
            null,
            null,
            new BillerInteractionCollection()
        );
    }

    /**
     * @param Transaction|null $previousTransaction Previous transaction
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public static function createQyssoRebillUpdateTransaction(
        ?Transaction $previousTransaction
    ): RebillUpdateTransaction {
        if (null !== $previousTransaction) {
            $billerChargeSettings = $previousTransaction->billerChargeSettings();
            $paymentInformation   = $previousTransaction->paymentInformation();
            $chargeInformation    = $previousTransaction->chargeInformation();
            $paymentType          = $previousTransaction->paymentType();
        }

        return new static(
            TransactionId::create(),
            QyssoBillerSettings::QYSSO,
            $billerChargeSettings ?? null,
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            Pending::create(),
            $previousTransaction,
            $paymentInformation ?? null,
            $chargeInformation ?? null,
            $paymentType ?? null,
            new BillerInteractionCollection()
        );
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return 'transaction';
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'type' => self::TYPE
            ]
        );
    }
}
