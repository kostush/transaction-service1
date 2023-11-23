<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommand;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\LookupThreeDsTwoAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;

class RocketgateLookupThreeDsTwoTranslatingService implements LookupThreeDsTwoTranslatingService
{
    /**
     * @var LookupThreeDsTwoAdapter
     */
    public $lookupAdapter;

    /**
     * RocketgateLookupTranslatingService constructor.
     * @param LookupThreeDsTwoAdapter $lookupAdapter LookupThreeDsTwoAdapter
     */
    public function __construct(LookupThreeDsTwoAdapter $lookupAdapter)
    {
        $this->lookupAdapter = $lookupAdapter;
    }

    /**
     * @param ChargeTransaction $transaction            Previous transaction
     * @param string            $cardNumber             Card number
     * @param string            $expirationMonth        Expiration month
     * @param string            $expirationYear         Expiration year
     * @param string            $cvv                    Cvv
     * @param string            $deviceFingerprintingId Device fingerprinting id
     * @param string            $returnUrl              Return url
     * @param string            $merchantAccount        Merchant account
     * @return BillerResponse
     * @throws InvalidPaymentInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function performLookup(
        ChargeTransaction $transaction,
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv,
        string $deviceFingerprintingId,
        string $returnUrl,
        string $merchantAccount
    ): BillerResponse {
        Log::info(
            'Preparing Rocketgate lookup for 3DS2 request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateLookupCommand = $this->createRocketgateLookupCommand(
            $transaction,
            $cardNumber,
            $expirationMonth,
            $expirationYear,
            $cvv,
            $deviceFingerprintingId,
            $returnUrl,
            $merchantAccount
        );

        return $this->lookupAdapter->performLookup($rocketgateLookupCommand, new \DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction            Previous transaction
     * @param string            $cardNumber             Card number
     * @param string            $expirationMonth        Expiration month
     * @param string            $expirationYear         Expiration year
     * @param string            $cvv                    Cvv
     * @param string            $deviceFingerprintingId Device fingerprinting id
     * @param string            $returnUrl              Return url
     * @param string            $merchantAccount        Merchant account
     * @return ThreeDSTwoLookupCommand
     * @throws InvalidPaymentInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function createRocketgateLookupCommand(
        ChargeTransaction $transaction,
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv,
        string $deviceFingerprintingId,
        string $returnUrl,
        string $merchantAccount
    ): ThreeDSTwoLookupCommand {
        if ($transaction->paymentInformation() instanceof CreditCardInformation) {
            $mandatoryFields = [
                'amount'                 => $transaction->chargeInformation()->amount()->value(),
                'currency'               => $transaction->chargeInformation()->currency()->code(),
                'number'                 => $cardNumber,
                'expirationMonth'        => (int) $expirationMonth,
                'expirationYear'         => (int) $expirationYear,
                'cvv'                    => $cvv,
                'merchantId'             => $transaction->billerChargeSettings()->merchantId(),
                'merchantPassword'       => $transaction->billerChargeSettings()->merchantPassword(),
                'deviceFingerPrintingId' => $deviceFingerprintingId,
                'threeDSRedirectUrl'     => $returnUrl,
                'merchantAccount'        => $merchantAccount
            ];

            return new ThreeDSTwoLookupCommand(
                (string) $transaction->transactionId(),
                $mandatoryFields,
                false,
                $this->prepareOptionalRebillDetails($transaction),
                $this->prepareOptionalCustomerDetails($transaction),
                $this->prepareOptionalBillerFields($transaction),
                true
            );
        }

        throw new InvalidPaymentInformationException();
    }


    /**
     * Prepare the rebill fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
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
     * Get the mandatory fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareOptionalBillerFields(ChargeTransaction $transaction): array
    {
        /** @var RocketGateChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        $merchantCustomerId = $billerChargeSettings->merchantCustomerId();
        $merchantInvoiceId  = $billerChargeSettings->merchantInvoiceId();

        // TODO done to align with the new TS
        $subsequentOperationFields = $transaction->subsequentOperationFieldsToArray();

        if ($subsequentOperationFields !== null) {
            if (empty($merchantCustomerId) && !empty($subsequentOperationFields['rocketgate']['merchantCustomerId'])) {
                $merchantCustomerId = $subsequentOperationFields['rocketgate']['merchantCustomerId'];
            }

            if (empty($merchantInvoiceId) && !empty($subsequentOperationFields['rocketgate']['merchantInvoiceId'])) {
                $merchantInvoiceId = $subsequentOperationFields['rocketgate']['merchantInvoiceId'];
            }
        }

        return [
            'merchantSiteId'     => $billerChargeSettings->merchantSiteId(),
            'merchantProductId'  => $billerChargeSettings->merchantProductId(),
            'merchantDescriptor' => $billerChargeSettings->merchantDescriptor(),
            'merchantCustomerId' => $merchantCustomerId,
            'merchantInvoiceId'  => $merchantInvoiceId,
            'ipAddress'          => $billerChargeSettings->ipAddress()
        ];
    }

    /**
     * Prepare the customer fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareOptionalCustomerDetails(ChargeTransaction $transaction): array
    {
        /** @var CreditCardInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

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
                    'address' => $paymentInformation->creditCardBillingAddress()->ownerAddress(),
                    'city'    => $paymentInformation->creditCardBillingAddress()->ownerCity(),
                    'state'   => $paymentInformation->creditCardBillingAddress()->ownerState(),
                    'zipCode' => $paymentInformation->creditCardBillingAddress()->ownerZip(),
                    'phone'   => $paymentInformation->creditCardBillingAddress()->ownerPhoneNo(),
                    'country' => $paymentInformation->creditCardBillingAddress()->ownerCountry()
                ]
            );
        }
        return $customerDetails;
    }
}
