<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTime;
use Exception;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

trait NetbillingPrepareBillerFieldsTrait
{
    /**
     * @param Transaction $transaction rebillUpdate transaction
     * @return array
     * @throws Exception
     */
    private function preparePaymentInformationFields(Transaction $transaction)
    {
        /** @var CreditCardInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        $cardExpirationDate = (new DateTime())
            ->setDate(
                $paymentInformation->expirationYear(),
                $paymentInformation->expirationMonth(),
                1
            );

        return [
            'amount'     => (string) $transaction->chargeInformation()->amount()->value(),
            'cardNumber' => $paymentInformation->creditCardNumber()->cardNumber(),
            'cardExpire' => $cardExpirationDate->format('my'),
            'cardCvv2'   => (string) $paymentInformation->cvv(),
            'payType'    => $transaction->paymentType(),
        ];
    }

    /**
     * @param Transaction $transaction Charge transaction
     * @return array|null
     */
    private function prepareRecurringBillingFields(Transaction $transaction)
    {
        $rebill = $transaction->chargeInformation()->rebill();

        if (!empty($rebill)) {
            return [
                'rebillAmount'    => (string) $rebill->amount()->value(),
                'rebillFrequency' => (string) $rebill->frequency(),
                'rebillStart'     => $rebill->start(),
            ];
        }

        return [];
    }

    /**
     * Get the mandatory fields for the netbilling request
     * @param Transaction $transaction Transaction entity
     * @return array
     */
    private function prepareBillerFields(Transaction $transaction): array
    {
        /** @var NetbillingChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        $billerFields = [
            'initialDays' => (string) $billerChargeSettings->initialDays(),
            'siteTag'     => $billerChargeSettings->siteTag(),
            'accountId'   => $billerChargeSettings->accountId(),
            'ipAddress'   => $billerChargeSettings->ipAddress(),
            'browser'     => $billerChargeSettings->browser(),
            'host'        => $billerChargeSettings->host(),
            'description' => $billerChargeSettings->description(),
            'routingCode' => $billerChargeSettings->binRouting()
        ];

        if ($billerChargeSettings instanceof NetbillingChargeSettings) {
            $billerFields['disableFraudChecks'] = $billerChargeSettings->disableFraudChecks();
        }

        return $billerFields;
    }

    /**
     * @param Transaction $transaction Charge transaction
     * @return array
     */
    private function prepareCustomerInformationFields(Transaction $transaction)
    {
        /** @var CreditCardInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        /** @var NetbillingChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        $customerDetails = [];

        if ($paymentInformation->creditCardOwner() instanceof CreditCardOwner) {
            $customerDetails = [
                'firstName' => $paymentInformation->creditCardOwner()->ownerFirstName(),
                'lastName'  => $paymentInformation->creditCardOwner()->ownerLastName(),
                'email'     => (string) $paymentInformation->creditCardOwner()->ownerEmail()
            ];
        }
        if ($paymentInformation->creditCardBillingAddress() instanceof CreditCardBillingAddress) {
            $customerDetails = array_merge(
                $customerDetails,
                [
                    'address'   => $paymentInformation->creditCardBillingAddress()->ownerAddress(),
                    'city'      => $paymentInformation->creditCardBillingAddress()->ownerCity(),
                    'state'     => $paymentInformation->creditCardBillingAddress()->ownerState(),
                    'zipCode'   => $paymentInformation->creditCardBillingAddress()->ownerZip(),
                    'phone'     => $paymentInformation->creditCardBillingAddress()->ownerPhoneNo(),
                    'country'   => $paymentInformation->creditCardBillingAddress()->ownerCountry(),
                    'ipAddress' => $billerChargeSettings->ipAddress(),
                    'host'      => $billerChargeSettings->host(),
                    'browser'   => $billerChargeSettings->browser()
                ]
            );
        }
        return $customerDetails;
    }

    /**
     * @param Transaction $transaction Charge transaction
     * @return array
     */
    private function prepareExistingPaymentInformationFields(Transaction $transaction)
    {
        /** @var NetbillingPaymentTemplateInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        return [
            'amount'     => (string) $transaction->chargeInformation()->amount()->value(),
            'cardNumber' => (string) $paymentInformation->netbillingCardHash(),
            'payType'    => $transaction->paymentType(),
        ];
    }

    /**
     * @param RebillUpdateTransaction $transaction Netbilling RebillUpdate Transaction
     * @return UpdateRebillCommand
     */
    protected function createNetbillingUpdateRebillCommand(
        RebillUpdateTransaction $transaction
    ): UpdateRebillCommand {
        return new UpdateRebillCommand(
            $transaction->billerChargeSettings()->billerMemberId(),
            $transaction->billerChargeSettings()->accountId(),
            $transaction->billerChargeSettings()->siteTag(),
            $transaction->billerChargeSettings()->merchantPassword(),
            (string) $transaction->chargeInformation()->rebill()->amount()->value(),
            (int) $transaction->chargeInformation()->rebill()->frequency(),
            null,
            $transaction->chargeInformation()->rebill()->start()
        );
    }

    /**
     * @param RebillUpdateTransaction $transaction Netbilling RebillUpdate Transaction
     * @param int|null                $templateTransId
     * @return UpdateRebillCommand
     */
    protected function createForNewCCNetbillingUpdateRebillCommand(
        RebillUpdateTransaction $transaction,
        ?int $templateTransId
    ): UpdateRebillCommand {
        return new UpdateRebillCommand(
            $transaction->billerChargeSettings()->billerMemberId(),
            $transaction->billerChargeSettings()->accountId(),
            $transaction->billerChargeSettings()->siteTag(),
            $transaction->billerChargeSettings()->merchantPassword(),
            (string) $transaction->chargeInformation()->rebill()->amount()->value(),
            (int) $transaction->chargeInformation()->rebill()->frequency(),
            $templateTransId,
            $transaction->chargeInformation()->rebill()->start()
        );
    }
}
