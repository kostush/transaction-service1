<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCheckCommand;
use ProBillerNG\Transaction\Domain\Model\AccountOwner;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CustomerBillingAddress;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;

/**
 * Class RocketgateOtherPaymentPayloadBuilder
 * @package ProBillerNG\Transaction\Infrastructure\Domain\Services
 */
class RocketgateOtherPaymentPayloadBuilder
{
    /**
     * @param ChargeTransaction $transaction
     *
     * @return ChargeWithNewCheckCommand
     * @throws InvalidPaymentInformationException
     */
    public function createRocketgateNewCheckChargeCommandFromChargeTransaction(ChargeTransaction $transaction
    ): ChargeWithNewCheckCommand {
        $mandatoryFields       = $this->prepareCommonMandatoryFields($transaction);
        $mandatoryCheckFields  = $this->prepareMandatoryFieldsForCheck($transaction);
        $optionalRebillDetails = $this->prepareOptionalRebillDetails($transaction);
        $optionalBillerDetails = $this->prepareOptionalBillerFields($transaction);

        if ($transaction->paymentInformation() instanceof CheckInformation) {
            return new ChargeWithNewCheckCommand(
                (string) $transaction->transactionId(),
                array_merge($mandatoryFields, $mandatoryCheckFields),
                env("BILLER_ROCKETGATE_TEST_MODE") ?? true,
                $this->prepareMandatoryCustomerDetails($transaction),
                $optionalRebillDetails,
                $optionalBillerDetails
            );
        }
        throw new InvalidPaymentInformationException();
    }

    /**
     * @param ChargeTransaction $transaction Transaction entity
     *
     * @return array
     */
    private function prepareCommonMandatoryFields(ChargeTransaction $transaction): array
    {
        return [
            // Charge information
            'amount'           => $transaction->chargeInformation()->amount()->value(),
            'currency'         => $transaction->chargeInformation()->currency()->code(),
            // Biller mandatory fields
            'merchantId'       => $transaction->billerChargeSettings()->merchantId(),
            'merchantPassword' => $transaction->billerChargeSettings()->merchantPassword(),
        ];
    }

    /**
     * @param ChargeTransaction $transaction
     *
     * @return array
     */
    private function prepareMandatoryFieldsForCheck(ChargeTransaction $transaction): array
    {
        return [
            // Payment information
            'routingNo'      => $transaction->paymentInformation()->routingNumber(),
            'accountNo'      => $transaction->paymentInformation()->accountNumber(),
            'savingsAccount' => $transaction->paymentInformation()->savingAccount(),
            'ssNumber'       => $transaction->paymentInformation()->socialSecurityLast4(),
        ];
    }

    /**
     * Prepare the rebill fields for the rocketgate request
     *
     * @param ChargeTransaction $transaction Transaction entity
     *
     * @return array|null
     */
    private function prepareOptionalRebillDetails(ChargeTransaction $transaction): ?array
    {
        $rebill = $transaction->chargeInformation()->rebill();

        if (!empty($rebill)) {
            return [
                'amount'    => $rebill->amount()->value(),
                'frequency' => $rebill->frequency(),
                'start'     => $rebill->start(),
            ];
        }

        return null;
    }

    /**
     * Prepare the customer fields for the rocketgate CHK request
     *
     * @param ChargeTransaction $transaction Transaction entity
     *
     * @return array
     */
    private function prepareMandatoryCustomerDetails(ChargeTransaction $transaction): array
    {
        /** @var CheckInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        $customerDetails = [];

        if ($paymentInformation->accountOwner() instanceof AccountOwner) {
            $customerDetails = [
                'firstName' => $paymentInformation->accountOwner()->ownerFirstName(),
                'lastName'  => $paymentInformation->accountOwner()->ownerLastName(),
                'email'     => (string) $paymentInformation->accountOwner()->ownerEmail()
            ];
        }
        if ($paymentInformation->customerBillingAddress() instanceof CustomerBillingAddress) {
            $customerDetails = array_merge(
                $customerDetails,
                [
                    'address' => $paymentInformation->customerBillingAddress()->ownerAddress(),
                    'city'    => $paymentInformation->customerBillingAddress()->ownerCity(),
                    'state'   => $paymentInformation->customerBillingAddress()->ownerState(),
                    'zipCode' => $paymentInformation->customerBillingAddress()->ownerZip(),
                    'phone'   => $paymentInformation->customerBillingAddress()->ownerPhoneNo(),
                    'country' => $paymentInformation->customerBillingAddress()->ownerCountry()
                ]
            );
        }

        return $customerDetails;
    }

    /**
     * Get the mandatory fields for the rocketgate request
     *
     * @param ChargeTransaction $transaction Transaction entity
     *
     * @return array
     */
    private function prepareOptionalBillerFields(ChargeTransaction $transaction): array
    {
        /** @var RocketGateChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        return [
            'merchantCustomerId' => $billerChargeSettings->merchantCustomerId(),
            'merchantInvoiceId'  => $billerChargeSettings->merchantInvoiceId(),
            'ipAddress'          => $billerChargeSettings->ipAddress()
        ];
    }
}