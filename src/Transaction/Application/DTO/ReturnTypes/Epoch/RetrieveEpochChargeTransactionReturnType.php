<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveEpochChargeTransactionReturnType
{
    const ACCEPTED_PAYMENT_INFORMATION_TYPES = [
        'newCreditCardTransaction' => CreditCardInformation::class,
    ];

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
     * @var EpochBillerTransactionCollection
     */
    private $billerTransactions;

    /**
     * RetrieveEpochChargeTransactionReturnType constructor.
     * @param string                           $siteId             The site id
     * @param string                           $billerId           The Epoch biler id
     * @param string                           $billerName         The Epoch biler name
     * @param TransactionReturnType            $transaction        The transaction
     * @param string                           $paymentType        The Epoch payment type
     * @param string                           $paymentMethod      The Epoch payment subtype
     * @param MemberReturnType                 $member             The Epoch member
     * @param array                            $billerSettings     The Epoch biller settings
     * @param EpochBillerTransactionCollection $billerTransactions The Epoch biller transactions
     * @param string                           $currency           The currency
     */
    private function __construct(
        string $siteId,
        string $billerId,
        string $billerName,
        TransactionReturnType $transaction,
        string $paymentType,
        string $paymentMethod,
        MemberReturnType $member,
        array $billerSettings,
        EpochBillerTransactionCollection $billerTransactions,
        string $currency
    ) {
        $this->siteId             = $siteId;
        $this->billerId           = $billerId;
        $this->billerName         = $billerName;
        $this->transaction        = $transaction;
        $this->paymentType        = $paymentType;
        $this->paymentMethod      = $paymentMethod;
        $this->member             = $member;
        $this->billerSettings     = $billerSettings;
        $this->billerTransactions = $billerTransactions->toArray();
        $this->currency           = $currency;
    }

    /**
     * @param Transaction $transaction The retrieved transaction
     * @return self
     * @throws InvalidPaymentInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function createFromEntity(
        Transaction $transaction
    ): self {
        //$className = get_class($transaction->paymentInformation());

        //if (!in_array($className, array_values(self::ACCEPTED_PAYMENT_INFORMATION_TYPES))) {
        //    throw new InvalidPaymentInformationException();
        //}

        $billerSettings     = $transaction->billerChargeSettings()->toArray();
        $billerInteractions = EpochBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $transaction->billerInteractions()
        );
        $billerTransactions = $billerInteractions->billerTransactions();
        $epochTransaction   = EpochCCTransactionReturnType::createFromTransaction($transaction);
        $member             = MemberReturnType::createFromBillerInteraction($billerInteractions);

        return new static(
            (string) $transaction->siteId(),
            $transaction->billerId(),
            $transaction->billerName(),
            $epochTransaction,
            $transaction->paymentType(),
            $transaction->paymentMethod(),
            $member,
            $billerSettings,
            $billerTransactions,
            $transaction->chargeInformation()->currency()->code()
        );
    }
}
