<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveQyssoRebillTransactionReturnType
{
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
     * @var string|null
     */
    private $billerTransactionId;

    /**
     * RetrieveQyssoRebillTransactionReturnType constructor.
     * @param string                           $siteId              Site Id
     * @param string                           $billerId            Biller Id
     * @param string                           $billerName          Biller Name
     * @param TransactionReturnType            $transaction         Transaction
     * @param string                           $paymentType         Payment Type
     * @param string|null                      $paymentMethod       Payment Method
     * @param QyssoMemberReturnType            $member              Member
     * @param array                            $billerSettings      Biller Settings
     * @param QyssoBillerTransactionCollection $billerTransactions  Biller Transactions
     * @param string                           $currency            Currency
     * @param string|null                      $billerTransactionId Biller Transaction Id
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
        ?string $billerTransactionId
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

        $initialBillerTransactionId = self::retrieveInitialBillerTransactionId($transaction->previousTransaction());
        $requestBillerTransaction   = QyssoBillerInteractionsReturnType::getRequestInteractions(
            $transaction->previousTransaction()->billerInteractions()->toArray()
        );

        $billerSettings = $transaction->previousTransaction()
                              ->billerChargeSettings() !== null ? $transaction->previousTransaction()
            ->billerChargeSettings()
            ->toArray() : [];

        $lastInteraction = end($requestBillerTransaction);
        $payload         = json_decode($lastInteraction->payload(), true);

        $billerInteractions = QyssoBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $transaction->billerInteractions(),
            self::TYPE_REBILL,
            $initialBillerTransactionId,
            $payload
        );

        $billerTransactions = $billerInteractions->billerTransactions();

        $qyssoTransaction = QyssoCCTransactionReturnType::createFromTransaction($transaction);
        $member           = QyssoMemberReturnType::createMemberInfoFromBillerInteraction($payload);

        $billerTransaction = $billerTransactions->toArray();
        $billerTransaction = end($billerTransaction);

        return new static(
            (string) ($transaction->siteId() ?? $transaction->previousTransaction()->siteId()),
            $transaction->billerId(),
            $transaction->billerName(),
            $qyssoTransaction,
            $transaction->paymentType(),
            $transaction->paymentMethod() ?? $transaction->previousTransaction()->paymentMethod(),
            $member,
            $billerSettings,
            $billerTransactions,
            $transaction->chargeInformation()->currency()->code(),
            $billerTransaction ? $billerTransaction->billerTransactionId() : null
        );
    }

    public static function retrieveInitialBillerTransactionId(Transaction $previousTransaction): string
    {
        $billerInteractions = QyssoBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $previousTransaction->billerInteractions(),
            self::TYPE_REBILL,
            null,
            []
        );

        $billerTransactions = $billerInteractions->billerTransactions();
        $billerTransaction  = $billerTransactions->toArray();
        $billerTransaction  = end($billerTransaction);

        return $billerTransaction->billerTransactionId();
    }

    /** TODO done to align with the new TS */
    /**
     * @return string
     */
    public function getEncodedBillerTransactions(): string
    {
        $billerTransactions = [];

        foreach ($this->billerTransactions as $billerTransaction) {
            $billerTransactions[] = $billerTransaction->toArray();
        }

        return json_encode($billerTransactions);
    }
}