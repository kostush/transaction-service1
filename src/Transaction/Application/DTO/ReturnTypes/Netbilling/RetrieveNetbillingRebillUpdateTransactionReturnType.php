<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\ExistingCreditCardTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\NewCreditCardTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;

class RetrieveNetbillingRebillUpdateTransactionReturnType extends RetrieveNetbillingChargeTransactionReturnType
{
    /** @var string|null */
    private $binRouting;

    private function __construct(
        string $billerId,
        string $billerName,
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $invoiceId,
        ?string $customerId,
        ?string $cardHash,
        ?string $transactionId,
        ?string $billerMemberId,
        string $currency,
        string $siteId,
        string $paymentType,
        ?string $merchantAccount,
        ?MemberReturnType $member,
        TransactionReturnType $transaction,
        ?int $cardExpirationYear,
        ?int $cardExpirationMonth,
        ?string $cardDescription,
        ?BillerSettings $billerSettings,
        ?string $binRouting,
        array $billerTransactions = []
    ) {
        parent::__construct($billerId, $billerName, $merchantId, $merchantPassword, $invoiceId, $customerId, $cardHash,
            $transactionId, $billerMemberId, $currency, $siteId, $paymentType, $merchantAccount, $member, $transaction,
            $cardExpirationYear, $cardExpirationMonth, $cardDescription, $billerSettings, $billerTransactions);
        $this->binRouting = $binRouting;
    }

    /**
     * @param Transaction $transaction
     * @return RetrieveNetbillingChargeTransactionReturnType
     * @throws InvalidPaymentInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function createFromEntity(Transaction $transaction): RetrieveNetbillingChargeTransactionReturnType
    {
        //Due to a lack of business requirements as a temporary measure this implementation is creating from previous
        //transaction in case of cancel transaction retrieval
        $paymentInformation = $transaction->paymentInformation();

        if ($paymentInformation == null) {
            $transaction = $transaction->previousTransaction();
        }

        $className = get_class($transaction->paymentInformation());

        if (!in_array($className, array_values(self::ACCEPTED_PAYMENT_INFORMATION_TYPES))) {
            throw new InvalidPaymentInformationException();
        }

        $memberPayload      = null;
        $expirationYear     = null;
        $expirationMonth    = null;
        $transactionPayload = null;
        $last4              = null;
        $cardHash           = null;
        $billerMemberId     = "";


        if (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['newCreditCardTransaction'] == $className) {
            $memberPayload      = MemberReturnType::createFromCreditCardInfo($transaction->paymentInformation());
            $transactionPayload = NewCreditCardTransactionReturnType::createFromTransaction($transaction);
            $expirationYear     = $transaction->paymentInformation()->expirationYear();
            $expirationMonth    = $transaction->paymentInformation()->expirationMonth();
            $last4              = $transactionPayload->last4();
        } elseif (self::ACCEPTED_PAYMENT_INFORMATION_TYPES['existingCreditCardTransaction'] == $className) {
            $transactionPayload = ExistingCreditCardTransactionReturnType::createFromTransaction($transaction);
            $cardHash           = base64_encode($transaction->paymentInformation()->netBillingCardHash()->value());
        }

        $billerInteractionFields = static::getNetbillingBillerInteractionFields(
            $transaction->billerInteractions(),
            $last4
        );

        if (empty($cardHash)) {
            $cardHash = $billerInteractionFields['cardHash'];
        }

        $billerSettingsPayload = $transaction->billerChargeSettings();
        $billerMemberId        = $billerInteractionFields['netbillingMemberId'];
        if (empty($billerMemberId) && $billerSettingsPayload != null) {
            $billerMemberId = $billerSettingsPayload->billerMemberId();
        }

        $billerInteractions = $transaction->billerInteractions()->toArray();
        $billerTransactions = [];

        if (count($billerInteractions) > 0) {
            self::sortBillerInteractions($billerInteractions);
            $billerInteractionResponses = self::getResponseInteractions($billerInteractions);
            $billerTransactions         = self::buildResponseData($billerInteractionResponses);
        }

        /** @var Transaction $transaction */
        return new static(
            $transaction->billerId(),
            $transaction->billerName(),
            "",
            "",
            "",
            "",
            $cardHash,
            $billerInteractionFields['billerTransactionId'],
            $billerMemberId,
            $billerInteractionFields['currency'] ?? "",
            (string) $transaction->siteId(),
            $transaction->paymentType(),
            "",
            $memberPayload,
            $transactionPayload,
            $expirationYear,
            $expirationMonth,
            $billerInteractionFields['cardDescription'],
            $billerSettingsPayload,
            $billerInteractionFields['binRouting'],
            $billerTransactions
        );
    }

    protected static function getNetbillingBillerInteractionFields($billerInteractions, $last4): array
    {
        $cardHash            = null;
        $billerTransactionId = null;
        $cardDescription     = null;
        $netbillingMemberId  = "";
        $binRouting          = "";
        $currency            = null;

        if ($billerInteractions->count()) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($billerInteractions as $billerInteraction) {
                $payload = json_decode($billerInteraction->payload());

                if (isset($payload->trans_id)) {
                    $billerTransactionId = $payload->trans_id;

                    $cardHash = base64_encode("CS:" . $billerTransactionId . ":" . $last4);
                }

                // The netbilling response payload contains snake_case and the request payload uses camelCase
                // Only one of either request or response will contain member id and never both
                // TODO: refactor and add tests for this class
                if (isset($payload->memberId) && empty($netbillingMemberId)) {
                    $netbillingMemberId = $payload->memberId;
                }
                if (isset($payload->member_id) && empty($netbillingMemberId)) {
                    $netbillingMemberId = $payload->member_id;
                }

                if (isset($payload->routingCode)) {
                    $binRouting = $payload->routingCode;
                }
                if (isset($payload->settle_currency)) {
                    $currency = $payload->settle_currency;
                }
            }
        }
        return [
            'cardHash'            => $cardHash,
            'billerTransactionId' => $billerTransactionId,
            'cardDescription'     => $cardDescription,
            'netbillingMemberId'  => $netbillingMemberId,
            'binRouting'          => $binRouting,
            'currency'            => $currency
        ];
    }
}
