<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionRequestResponseInteractionTrait;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use stdClass;

class RetrieveLegacyChargeTransactionReturnType
{
    use TransactionRequestResponseInteractionTrait;

    /** @var string */
    private $billerId;

    /** @var string */
    private $billerName;

    /** @var string */
    private $siteId;

    /** @var string */
    private $currency;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $paymentMethod;

    /** @var TransactionReturnType */
    private $transaction;

    /** @var null|MemberReturnType */
    private $member;

    /** @var array */
    private $billerSettings;

    /** @var null|array */
    private $billerTransactions;

    /**
     * RetrieveLegacyChargeTransactionReturnType constructor.
     * @param string                $billerId           biller id
     * @param string                $billerName         biller name
     * @param string                $siteId             site id
     * @param string                $currency           currency
     * @param string                $paymentType        payment type
     * @param string|null           $paymentMethod      payment method
     * @param TransactionReturnType $transaction        transaction return type
     * @param BillerSettings        $billerSettings     biller settings
     * @param array|null            $billerTransactions biller transaction
     * @param MemberReturnType|null $member             member info
     */
    private function __construct(
        string $billerId,
        string $billerName,
        string $siteId,
        string $currency,
        string $paymentType,
        ?string $paymentMethod,
        TransactionReturnType $transaction,
        BillerSettings $billerSettings,
        ?array $billerTransactions,
        ?MemberReturnType $member
    ) {
        $this->billerId           = $billerId;
        $this->billerName         = $billerName;
        $this->siteId             = $siteId;
        $this->currency           = $currency;
        $this->paymentType        = $paymentType;
        $this->paymentMethod      = $paymentMethod;
        $this->transaction        = $transaction;
        $this->member             = $member;
        $this->billerSettings     = $billerSettings;
        $this->billerTransactions = $billerTransactions;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RetrieveLegacyChargeTransactionReturnType
     */
    public static function createFromEntity(Transaction $transaction): RetrieveLegacyChargeTransactionReturnType
    {
        $billerTransactions = [];
        $member             = null;
        $transactionData    = LegacyTransactionReturnType::createFromTransaction($transaction);

        $billerInteractions = $transaction->billerInteractions()->toArray();
        if (count($billerInteractions) > 0) {
            self::sortBillerInteractions($billerInteractions);
            $billerInteractionRequests = self::getRequestInteractions($billerInteractions);
            $billerRequestData         = self::buildRequestData($billerInteractionRequests);
            $member                    = self::buildMemberData($billerRequestData);
            $billerTransactions        = self::buildBillerInteraction($billerInteractions);
        }

        /** @var Transaction $transaction */
        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            (string) $transaction->siteId(),
            $transaction->chargeInformation()->currency()->code(),
            $transaction->paymentType(),
            $transaction->paymentMethod(),
            $transactionData,
            $transaction->billerChargeSettings(),
            $billerTransactions,
            $member
        );
    }

    /**
     * @param null|stdClass $billerRequestData biller request
     * @return MemberReturnType|null
     */
    public static function buildMemberData(?stdClass $billerRequestData): ?MemberReturnType
    {
        if (!empty($billerRequestData) && isset($billerRequestData->payment->information->member)) {
            if (!empty($billerRequestData->payment->information->member)) {
                return MemberReturnType::createMemberInfoFromBillerInteraction(
                    json_decode(json_encode($billerRequestData->payment->information->member), true)
                );
            }
        }

        return null;
    }

    /**
     * @param array|null $requestInteraction The biller interaction object
     * @return object|null
     */
    public static function buildRequestData(?array $requestInteraction): ?stdClass
    {
        if (!empty($requestInteraction)) {
            return json_decode($requestInteraction[0]->payload());
        }

        return null;
    }

    /**
     * @param array $billerInteractionCollection biller collections
     * @return array
     */
    public static function buildBillerInteraction(array $billerInteractionCollection): array
    {
        $interactions = [];
        /** @var BillerInteraction $billerInteraction */
        foreach ($billerInteractionCollection as $key => $billerInteraction) {
            $interactions[$key]['payload'] = json_decode($billerInteraction->payload(), true);
            $interactions[$key]['type']    = $billerInteraction->type();
        }

        return $interactions;
    }
}
