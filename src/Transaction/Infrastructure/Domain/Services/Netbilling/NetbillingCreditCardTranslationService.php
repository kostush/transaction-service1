<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Services\CreditCardCharge;
use ProBillerNG\Transaction\Infrastructure\Domain\ExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\NewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;

class NetbillingCreditCardTranslationService implements CreditCardCharge
{
    use NetbillingPrepareBillerFieldsTrait;

    /** @var NewCreditCardChargeAdapter */
    protected $newCreditCardAdapter;

    /** @var ExistingCreditCardChargeAdapter */
    protected $existingCreditCardAdapter;

    /** @var BaseNetbillingCancelRebillAdapter */
    protected $cancelRebillAdapter;

    /**
     * NetbillingCreditCardTranslationService constructor.
     * @param NetbillingNewCreditCardChargeAdapter      $newCreditCardChargeAdapter New Card Adapter
     * @param NetbillingExistingCreditCardChargeAdapter $existingCreditCardAdapter  Existing Card Adapter
     * @param BaseNetbillingCancelRebillAdapter         $cancelRebillAdapter        Cancel Rebill Adapter
     */
    public function __construct(
        NetbillingNewCreditCardChargeAdapter $newCreditCardChargeAdapter,
        NetbillingExistingCreditCardChargeAdapter $existingCreditCardAdapter,
        BaseNetbillingCancelRebillAdapter $cancelRebillAdapter
    ) {
        $this->newCreditCardAdapter      = $newCreditCardChargeAdapter;
        $this->existingCreditCardAdapter = $existingCreditCardAdapter;
        $this->cancelRebillAdapter       = $cancelRebillAdapter;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     * @throws \Exception
     */
    public function chargeWithNewCreditCard(ChargeTransaction $transaction)
    {
        Log::info(
            'Preparing Netbilling new credit card charge request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingChargeCommand = $this->createNetbillingNewCreditCardChargeCommand($transaction);

        return $this->newCreditCardAdapter->charge($netbillingChargeCommand, new \DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     * @throws InvalidPaymentInformationException
     * @throws LoggerException
     * @throws Exception
     */
    public function chargeWithExistingCreditCard(ChargeTransaction $transaction)
    {
        Log::info(
            'Preparing Netbilling existing card charge request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingChargeCommand = $this->createNetbillingExistingCreditCardChargeCommand($transaction);

        return $this->existingCreditCardAdapter->charge($netbillingChargeCommand, new \DateTimeImmutable());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     * @throws LoggerException
     */
    public function suspendRebill(RebillUpdateTransaction $transaction)
    {
        Log::info(
            'Preparing Netbilling cancel rebill request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingSuspendRebillCommand = $this->createNetbillingSuspendRebillCommand($transaction);

        return $this->cancelRebillAdapter->cancel($netbillingSuspendRebillCommand, new \DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction transaction
     * @return CreditCardChargeCommand
     * @throws InvalidPaymentInformationException
     * @throws LoggerException
     * @throws \Exception
     */
    protected function createNetbillingNewCreditCardChargeCommand(
        ChargeTransaction $transaction
    ): CreditCardChargeCommand {
        $billerFields              = $this->prepareBillerFields($transaction);
        $membershipFields          = $this->prepareMembershipFields($transaction);
        $paymentInformationFields  = $this->preparePaymentInformationFields($transaction);
        $recurringBillingFields    = $this->prepareRecurringBillingFields($transaction);
        $customerInformationFields = $this->prepareCustomerInformationFields($transaction);

        if ($transaction->paymentInformation() instanceof CreditCardInformation) {
            return new CreditCardChargeCommand(
                (string) $transaction->transactionId(),
                $billerFields,
                $membershipFields,
                $paymentInformationFields,
                $recurringBillingFields,
                $customerInformationFields
            );
        }
        throw new InvalidPaymentInformationException();
    }

    /**
     * @param RebillUpdateTransaction $transaction
     * @return CancelRebillCommand
     */
    protected function createNetbillingSuspendRebillCommand(
        RebillUpdateTransaction $transaction
    ): CancelRebillCommand {
        return new CancelRebillCommand(
            $transaction->billerChargeSettings()->billerMemberId(),
            $transaction->billerChargeSettings()->accountId(),
            $transaction->billerChargeSettings()->siteTag(),
            $transaction->billerChargeSettings()->merchantPassword(),
        );
    }


    /**
     * @param ChargeTransaction $transaction Charge transaction
     * @return array
     */
    private function prepareMembershipFields(ChargeTransaction $transaction): array
    {
        /** @var CreditCardInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        $membershipFields = [];

        if ($paymentInformation->creditCardOwner() instanceof CreditCardOwner) {
            $membershipFields = [
                'memberUsername' => $paymentInformation->creditCardOwner()->ownerUserName(),
                'memberPassword' => $paymentInformation->creditCardOwner()->ownerPassword(),
                'memberId' => $transaction->billerChargeSettings()->billerMemberId()
            ];
        }

        return $membershipFields;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return CreditCardChargeCommand
     * @throws InvalidPaymentInformationException
     * @throws LoggerException
     * @throws Exception
     */
    private function createNetbillingExistingCreditCardChargeCommand(
        ChargeTransaction $transaction
    ): CreditCardChargeCommand {
        $billerFields              = $this->prepareBillerFields($transaction);
        $recurringBillingFields    = $this->prepareRecurringBillingFields($transaction);
        $paymentInformationFields  = $this->prepareExistingPaymentInformationFields($transaction);
        $customerInformationFields = $this->prepareExistingCustomerInformationFields($transaction);
        $memberInformation         = $this->prepareExistingCardMemberInformationFields($transaction);

        if ($transaction->paymentInformation() instanceof NetbillingPaymentTemplateInformation) {
            return new CreditCardChargeCommand(
                (string) $transaction->transactionId(),
                $billerFields,
                $memberInformation,
                $paymentInformationFields,
                $recurringBillingFields,
                $customerInformationFields
            );
        }
        throw new InvalidPaymentInformationException();
    }

    /**
     * @param ChargeTransaction $transaction Charge transaction
     * @return array
     */
    private function prepareExistingCustomerInformationFields(ChargeTransaction $transaction)
    {
        /** @var NetbillingChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        $customerDetails = [];

        if ($billerChargeSettings instanceof NetbillingChargeSettings) {
            $customerDetails = array_merge(
                $customerDetails,
                [
                    'ipAddress' => $billerChargeSettings->ipAddress(),
                    'host'      => $billerChargeSettings->host(),
                    'browser'   => $billerChargeSettings->browser()
                ]
            );
        }
        return $customerDetails;
    }

    /**
     * @param ChargeTransaction $transaction existing card purchase transaction
     * @return array
     */
    private function prepareExistingCardMemberInformationFields(ChargeTransaction $transaction)
    {
        $membershipFields = [];
        $billerMember     = $transaction->billerMember();
        if (!empty($billerMember)) {
            return [
                'memberUsername' => $billerMember->userName(),
                'memberPassword' => $billerMember->password()
            ];
        }
        return $membershipFields;
    }
}
