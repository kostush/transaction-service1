<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveQyssoChargeTransactionReturnType
{
    public const TYPE_CHARGE = 'charge';
    public const TYPE_REBILL = 'rebill';

    /**
     * @var string
     */
    private $siteId;

    /**
     * @var string
     */
    private $billerId;

    /**
     * @var string
     */
    private $billerName;

    /**
     * @var TransactionReturnType
     */
    private $transaction;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var MemberReturnType
     */
    private $member;

    /**
     * @var array
     */
    private $billerSettings;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var QyssoBillerTransactionCollection
     */
    private $billerTransactions;

    /**
     * @var string
     */
    private $billerTransactionId;

    /**
     * RetrieveQyssoChargeTransactionReturnType constructor.
     * @param string                           $siteId              The site id
     * @param string                           $billerId            The Qysso biller id
     * @param string                           $billerName          The Qysso biller name
     * @param TransactionReturnType            $transaction         The transaction
     * @param string                           $paymentType         The Qysso payment type
     * @param string                           $paymentMethod       The Qysso payment subtype
     * @param QyssoMemberReturnType            $member              The Qysso member
     * @param array                            $billerSettings      The Qysso biller settings
     * @param QyssoBillerTransactionCollection $billerTransactions  The Qysso biller transactions
     * @param string                           $currency            The currency
     * @param string                           $billerTransactionId The Qysso transaction id.
     */
    private function __construct(
        string $siteId,
        string $billerId,
        string $billerName,
        TransactionReturnType $transaction,
        string $paymentType,
        ?string $paymentMethod,
        QyssoMemberReturnType $member,
        array $billerSettings,
        QyssoBillerTransactionCollection $billerTransactions,
        string $currency,
        string $billerTransactionId
    ) {
        $this->siteId              = $siteId;
        $this->billerId            = $billerId;
        $this->billerName          = $billerName;
        $this->transaction         = $transaction;
        $this->paymentType         = $paymentType;
        $this->paymentMethod       = $paymentMethod;
        $this->member              = $member;
        $this->billerSettings      = $billerSettings;
        $this->billerTransactions  = $billerTransactions->toArray();
        $this->currency            = $currency;
        $this->billerTransactionId = $billerTransactionId;
    }

    /**
     * @param Transaction $transaction The retrieved transaction
     * @return self
     */
    public static function createFromEntity(
        Transaction $transaction
    ): self {

        $requestBillerTransaction = QyssoBillerInteractionsReturnType::getRequestInteractions(
            $transaction->billerInteractions()->toArray()
        );

        $billerSettings = $transaction->billerChargeSettings() !== null ? $transaction->billerChargeSettings()
            ->toArray() : [];

        $lastInteraction = end($requestBillerTransaction);
        $payload         = json_decode($lastInteraction->payload(), true);

        $billerInteractions = QyssoBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $transaction->billerInteractions(),
            self::TYPE_CHARGE,
            null,
            $payload
        );

        $billerTransactions = $billerInteractions->billerTransactions();
        $qyssoTransaction   = QyssoCCTransactionReturnType::createFromTransaction($transaction);
        $member             = QyssoMemberReturnType::createMemberInfoFromBillerInteraction($payload);

        $billerTransaction = $billerTransactions->toArray();
        $billerTransaction = end($billerTransaction);

        return new static(
            (string) $transaction->siteId(),
            $transaction->billerId(),
            $transaction->billerName(),
            $qyssoTransaction,
            $transaction->paymentType(),
            $transaction->paymentMethod(),
            $member,
            $billerSettings,
            $billerTransactions,
            $transaction->chargeInformation()->currency()->code(),
            $billerTransaction->billerTransactionId()
        );
    }

    /** TODO done to align with the new TS */
    /**
     * @param string|null $billerTransactions Existing biller transactions.
     * @return string
     */
    public function getEncodedBillerTransactions(?string $billerTransactions = null): string
    {
        if ($billerTransactions !== null) {
            $billerTransactions = json_decode($billerTransactions, true);
        }

        foreach ($this->billerTransactions as $billerTransaction) {
            $billerTransactions[] = $billerTransaction->toArray();
        }

        return json_encode($billerTransactions);
    }
}
