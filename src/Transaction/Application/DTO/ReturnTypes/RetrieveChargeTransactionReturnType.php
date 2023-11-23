<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerTransactionCollection;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;

class RetrieveChargeTransactionReturnType extends RetrieveTransactionReturnType
{
    public const ACCEPTED_PAYMENT_INFORMATION_TYPES = [
        'newCreditCardTransaction'      => CreditCardInformation::class,
        'existingCreditCardTransaction' => PaymentTemplateInformation::class,
        'checkTransaction'              => CheckInformation::class
    ];

    /**
     * @var string
     * @deprecated
     */
    private $transactionId;

    /** @var string */
    private $currency;

    /** @var string */
    private $siteId;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $merchantAccount;

    /** @var MemberReturnType */
    private $member;

    /** @var int */
    private $cardExpirationYear;

    /** @var int */
    private $cardExpirationMonth;

    /** @var string $cardDescription */
    private $cardDescription;

    /**
     * @var array|null
     */
    private $billerSettings;

    /**
     * @var string|null
     */
    private $billerMemberId;

    /**
     * @var array
     */
    private $billerTransactions = [];

    /**
     * @var bool
     */
    private $securedWithThreeD = false;

    /**
     * @var int|null
     */
    private $threedSecuredVersion;

    /**
     * @var string|null
     */
    private $routingNumber;

    /**
     * @var string|null
     */
    private $accountNumber;

    /**
     * @var string|null
     */
    private $socialSecurityLast4;

    /**
     * TransactionPayload constructor.
     *
     * @param string                                $billerId                    Biller id.
     * @param string                                $billerName                  Biller Name.
     * @param string                                $merchantId                  Merchant id.
     * @param string                                $merchantPassword            Merchant password.
     * @param null|string                           $invoiceId                   Invoice id.
     * @param null|string                           $customerId                  Customer id.
     * @param null|string                           $cardHash                    Card hash.
     * @param null|string                           $transactionId               Transaction id.
     * @param null|string                           $billerMemberId              Biller member id.
     * @param string                                $currency                    Currency.
     * @param string                                $siteId                      Site id.
     * @param string                                $paymentType                 Payment type.
     * @param string                                $merchantAccount             Merchant account.
     * @param MemberReturnType|null                 $member                      Member.
     * @param TransactionReturnType                 $transaction                 Transaction.
     * @param int|null                              $cardExpirationYear          Card expiration year.
     * @param int|null                              $cardExpirationMonth         Card expiration month.
     * @param string|null                           $cardDescription             Card description.
     * @param BillerSettings|null                   $billerSettings              Biller settings.
     * @param RocketgateBillerTransactionCollection $billerTransactionCollection The biller transaction collection.
     * @param bool                                  $securedWithThreeD           The 3DS flag.
     * @param int|null                              $threeDSecuredVersion        The 3DS version.
     * @param string|null                           $routingNumber               Routing number
     * @param string|null                           $accountNumber               Account number
     * @param string|null                           $socialSecurityLast4         Social Security Last 4 digits
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
        ?string $billerMemberId,
        string $currency,
        string $siteId,
        string $paymentType,
        string $merchantAccount,
        ?MemberReturnType $member,
        TransactionReturnType $transaction,
        ?int $cardExpirationYear,
        ?int $cardExpirationMonth,
        ?string $cardDescription,
        ?BillerSettings $billerSettings,
        RocketgateBillerTransactionCollection $billerTransactionCollection,
        bool $securedWithThreeD,
        ?int $threeDSecuredVersion,
        ?string $routingNumber,
        ?string $accountNumber,
        ?string $socialSecurityLast4
    ) {
        $this->billerId             = $billerId;
        $this->billerName           = $billerName;
        $this->merchantId           = $merchantId;
        $this->merchantPassword     = $merchantPassword;
        $this->invoiceId            = $invoiceId;
        $this->customerId           = $customerId;
        $this->cardHash             = $cardHash;
        $this->transactionId        = $transactionId;
        $this->billerTransactionId  = $transactionId;
        $this->billerMemberId       = $billerMemberId;
        $this->currency             = $currency;
        $this->siteId               = $siteId;
        $this->paymentType          = $paymentType;
        $this->merchantAccount      = $merchantAccount;
        $this->member               = $member;
        $this->transaction          = $transaction;
        $this->cardExpirationYear   = $cardExpirationYear;
        $this->cardExpirationMonth  = $cardExpirationMonth;
        $this->cardDescription      = $cardDescription;
        $this->billerSettings       = $billerSettings;
        $this->billerTransactions   = $billerTransactionCollection->toArray();
        $this->securedWithThreeD    = $securedWithThreeD;
        $this->threedSecuredVersion = $threeDSecuredVersion;
        $this->routingNumber        = $routingNumber;
        $this->accountNumber        = $accountNumber;
        $this->socialSecurityLast4  = $socialSecurityLast4;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return RetrieveChargeTransactionReturnType
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    public static function createFromEntity(Transaction $transaction): RetrieveChargeTransactionReturnType
    {
        $className = get_class($transaction->paymentInformation());

        if (!in_array($className, array_values(self::ACCEPTED_PAYMENT_INFORMATION_TYPES))) {
            throw new InvalidPaymentInformationException();
        }

        $memberPayload       = null;
        $expirationYear      = null;
        $expirationMonth     = null;
        $transactionPayload  = null;
        $routingNumber       = null;
        $accountNumber       = null;
        $socialSecurityLast4 = null;
        if (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['newCreditCardTransaction'] == $className) {
            $memberPayload      = MemberReturnType::createFromCreditCardInfo($transaction->paymentInformation());
            $transactionPayload = NewCreditCardTransactionReturnType::createFromTransaction($transaction);
            $expirationYear     = $transaction->paymentInformation()->expirationYear();
            $expirationMonth    = $transaction->paymentInformation()->expirationMonth();
        } elseif (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['existingCreditCardTransaction'] == $className) {
            $transactionPayload = ExistingCreditCardTransactionReturnType::createFromTransaction($transaction);
        } elseif (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['checkTransaction'] == $className) {
            $memberPayload       = MemberReturnType::createFromCheckInfo($transaction->paymentInformation());
            $transactionPayload  = CheckTransactionReturnType::createFromTransaction($transaction);
            $routingNumber       = $transaction->paymentInformation()->routingNumber();
            $accountNumber       = $transaction->paymentInformation()->accountNumber();
            $socialSecurityLast4 = $transaction->paymentInformation()->socialSecurityLast4();
        }

        $billerInteractionResponse = self::retrieveBillerResponse($transaction);

        if (!empty($billerInteractionResponse) && $transaction->status() instanceof Declined) {
            $transactionPayload->setCode((int) $billerInteractionResponse['reasonCode']);
            $transactionPayload->setReason(
                (string) RocketgateErrorCodes::getMessage((int) $billerInteractionResponse['reasonCode'])
            );
        }

        $threeDSecured = $transaction->threedsVersion() > 0 ? true : false;

        $billerInteractions = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $transaction->billerInteractions(),
            $threeDSecured
        );

        $billerSettingsPayload = $transaction->billerChargeSettings();

        $billerTransactionId = !$billerInteractions->billerTransactions()
            ->first() ? null : $billerInteractions->billerTransactions()->first()->billerTransactionId();

        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            $transaction->billerChargeSettings()->merchantId(),
            $transaction->billerChargeSettings()->merchantPassword(),
            $transaction->billerChargeSettings()->merchantInvoiceId(),
            $transaction->billerChargeSettings()->merchantCustomerId(),
            $billerInteractions->cardHash(),
            $billerTransactionId,
            '',
            $transaction->chargeInformation()->currency()->code(),
            (string) $transaction->siteId(),
            $transaction->paymentType(),
            $transaction->billerChargeSettings()->merchantAccount() ?? '', // TODO done to align with the new TS
            $memberPayload,
            $transactionPayload,
            $expirationYear,
            $expirationMonth,
            $billerInteractions->cardDescription(),
            $billerSettingsPayload,
            $billerInteractions->billerTransactions(),
            $threeDSecured,
            $transaction->threedsVersion(),
            $routingNumber,
            $accountNumber,
            $socialSecurityLast4
        );
    }
}
