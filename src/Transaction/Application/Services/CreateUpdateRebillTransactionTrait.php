<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\PreviousTransactionCorruptedDataException;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateUpdateRebillBillerFields;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Currency;
use ProBillerNG\Transaction\Domain\Model\Email;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebill;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebillUpdateSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateCardHash;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;

trait CreateUpdateRebillTransactionTrait
{
    use BillerResponseAttributeExtractorTrait;

    /**
     * @param PerformRocketgateUpdateRebillCommand $command             PerformRocketgateUpdateRebillCommand
     * @param Transaction                          $previousTransaction PreviousTransaction
     * @return RebillUpdateTransaction
     * @throws \Exception
     */
    protected function createRocketgateUpdateRebillTransaction(
        PerformRocketgateUpdateRebillCommand $command,
        Transaction $previousTransaction
    ): RebillUpdateTransaction {

        return RebillUpdateTransaction::createUpdateRebillTransaction(
            $previousTransaction,
            RocketGateBillerSettings::ROCKETGATE,
            new RocketGateUpdateRebillBillerFields(
                $command->merchantId(),
                $command->merchantPassword(),
                $command->merchantCustomerId(),
                $command->merchantInvoiceId(),
                $command->merchantAccount()
            ),
            $this->addPaymentInformation($command, $previousTransaction),
            ChargeInformation::createWithRebill(
                $command->currency() ? Currency::create($command->currency()) : null,
                Amount::create($command->amount()),
                Rebill::create(
                    $command->rebillFrequency(),
                    $command->rebillStart(),
                    Amount::create($command->rebillAmount())
                )
            ),
            $command->paymentType()
        );
    }

    /**
     * @param PerformNetbillingUpdateRebillCommand $command             Command
     * @param Transaction                          $previousTransaction Previous Transaction
     *
     * @return RebillUpdateTransaction
     * @throws InvalidCreditCardInformationException
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    protected function createNetbillingUpdateRebillTransaction(
        PerformNetbillingUpdateRebillCommand $command,
        Transaction $previousTransaction
    ): RebillUpdateTransaction {

        $billerMemberId = $this->getNetbillingBillerMemberIdFromTransaction($previousTransaction);

        try {
            return RebillUpdateTransaction::createNetbillingUpdateRebillTransaction(
                $previousTransaction,
                NetbillingBillerSettings::NETBILLING,
                $this->createBillerSettings($command, $billerMemberId),
                $this->createNetbillingPaymentInformation($command),
                ChargeInformation::createWithRebill(
                    $command->currency() ? Currency::create($command->currency()) : null,
                    Amount::create($command->amount()),
                    NetbillingRebill::create(
                        $command->rebillFrequency(),
                        $command->rebillStart(),
                        Amount::create($command->rebillAmount())
                    )
                ),
                $command->paymentType()
            );
        } catch (InvalidCreditCardInformationException $e) {
            Log::logException($e);

            throw new InvalidCreditCardInformationException('cardHash does not match the valid pattern');
        }
    }

    /**
     * @param Transaction $previousTransaction Previous Transaction
     * @return string
     * @throws PreviousTransactionCorruptedDataException
     * @throws Exception
     */
    public function getNetbillingBillerMemberIdFromTransaction(Transaction $previousTransaction): string
    {
        $billerInteractions = $previousTransaction->billerInteractions();

        $netbillingMemberId = "";

        if ($billerInteractions->count()) {
            /** @var BillerInteraction $billerInteraction */
            foreach ($billerInteractions as $billerInteraction) {
                if ($billerInteraction->type() == BillerInteraction::TYPE_REQUEST) {
                    $payload = json_decode($billerInteraction->payload());
                    if (isset($payload->memberId)) {
                        $netbillingMemberId = $payload->memberId;
                    }
                }

                if ($billerInteraction->type() == BillerInteraction::TYPE_RESPONSE) {
                    $payload = json_decode($billerInteraction->payload());

                    if (isset($payload->member_id)) {
                        $netbillingMemberId = $payload->member_id;
                    }
                    break;
                }
            }
        }

        $billerSettingsPayload = $previousTransaction->billerChargeSettings();
        /** @var $billerSettingsPayload NetbillingChargeSettings */
        if (empty($netbillingMemberId) && $billerSettingsPayload != null) {
            $netbillingMemberId = $billerSettingsPayload->billerMemberId();
        }

        if (empty($netbillingMemberId) && !is_null($previousTransaction->subsequentOperationFieldsToArray())) {
            if (isset($previousTransaction->subsequentOperationFieldsToArray()['netbilling']['billerMemberId'])) {
                $netbillingMemberId = (string) $previousTransaction->subsequentOperationFieldsToArray()['netbilling']['billerMemberId'];
            }
        }

        if (empty($netbillingMemberId)) {
            throw new PreviousTransactionCorruptedDataException('billerMemberId');
        }

        return $netbillingMemberId;
    }

    /**
     * @param PerformNetbillingUpdateRebillCommand $command rebill update command
     *
     * @return PaymentInformation
     * @throws InvalidCreditCardInformationException
     * @throws Exception
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidPaymentInformationException
     * @throw  InvalidCreditCardExpirationDateException
     */
    protected function createNetbillingPaymentInformation(
        PerformNetbillingUpdateRebillCommand $command
    ): PaymentInformation {

        $payment = $command->payment();
        if ($payment->information() instanceof NewCreditCardInformation) {
            return CreditCardInformation::create(
                true,
                CreditCardNumber::create($payment->information()->number()),
                $this->createCreditCardOwner($payment),
                $this->createCreditCardBillingAddress($payment),
                $payment->information()->cvv(),
                $payment->information()->expirationMonth(),
                $payment->information()->expirationYear()
            );
        }

        if ($payment->information() instanceof ExistingCreditCardInformation) {
            return NetbillingPaymentTemplateInformation::create(
                NetbillingCardHash::create($payment->information()->cardHash())
            );
        }

        throw new InvalidPaymentInformationException();
    }

    /**
     * @param Payment $payment Payment
     * @return CreditCardOwner|null
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     */
    protected function createCreditCardOwner(Payment $payment): ?CreditCardOwner
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return CreditCardOwner::create(
                $payment->information()->member()->firstName(),
                $payment->information()->member()->lastName(),
                Email::create($payment->information()->member()->email()),
                $payment->information()->member()->userName(),
                $payment->information()->member()->password()
            );
        }

        return null;
    }

    /**
     * @param Payment $payment payment
     * @return CreditCardBillingAddress|null
     */
    protected function createCreditCardBillingAddress(Payment $payment): ?CreditCardBillingAddress
    {
        if (method_exists($payment->information(), 'member')
            && !empty($payment->information()->member())
        ) {
            return CreditCardBillingAddress::create(
                $payment->information()->member()->address(),
                $payment->information()->member()->city(),
                $payment->information()->member()->country(),
                $payment->information()->member()->state(),
                $payment->information()->member()->zipCode(),
                $payment->information()->member()->phone()
            );
        }

        return null;
    }

    /**
     * @param PerformNetbillingUpdateRebillCommand $command        rebill update command
     * @param string                               $billerMemberId biller member id
     * @return NetbillingRebillUpdateSettings
     */
    protected function createBillerSettings(
        PerformNetbillingUpdateRebillCommand $command,
        string $billerMemberId
    ): NetbillingRebillUpdateSettings {
        return NetbillingRebillUpdateSettings::create(
            $command->siteTag(),
            $command->accountId(),
            $billerMemberId,
            $command->merchantPassword(),
            $command->initialDays(),
            $command->binRouting()
        );
    }

    /**
     * @param PerformRocketgateUpdateRebillCommand $command             PerformRocketgateUpdateRebillCommand
     * @param Transaction                          $previousTransaction PreviousTransaction
     * @return RebillUpdateTransaction
     * @throws \Exception
     */
    protected function createRocketgateStopRebillTransaction(
        PerformRocketgateUpdateRebillCommand $command,
        Transaction $previousTransaction
    ): RebillUpdateTransaction {

        return RebillUpdateTransaction::createUpdateRebillTransaction(
            $previousTransaction,
            RocketGateBillerSettings::ROCKETGATE,
            new RocketGateUpdateRebillBillerFields(
                $command->merchantId(),
                $command->merchantPassword(),
                $command->merchantCustomerId(),
                $command->merchantInvoiceId(),
                $command->merchantAccount()
            ),
            $this->addPaymentInformation($command, $previousTransaction),
            ChargeInformation::createSingleCharge(
                $command->currency() ? Currency::create($command->currency()) : null,
                Amount::create($command->amount())
            ),
            $command->paymentType()
        );
    }

    /**
     * @param PerformRocketgateUpdateRebillCommand $command             PerformRocketgateUpdateRebillCommand
     * @param Transaction                          $previousTransaction PreviousTransaction
     * @return void
     * @throws InvalidMerchantInformationException
     */
    protected function validateBillerFiels(
        PerformRocketgateUpdateRebillCommand $command,
        Transaction $previousTransaction
    ): void {
        /** @var RocketGateChargeSettings $billerChargeSettings */
        $billerChargeSettings = $previousTransaction->billerChargeSettings();

        $merchantCustomerId        = $billerChargeSettings->merchantCustomerId();
        $merchantInvoiceId         = $billerChargeSettings->merchantInvoiceId();
        $subsequentOperationFields = $previousTransaction->subsequentOperationFieldsToArray();

        if ($subsequentOperationFields !== null) {
            if (!empty($subsequentOperationFields['rocketgate']['merchantCustomerId'])
            ) {
                $merchantCustomerId = $subsequentOperationFields['rocketgate']['merchantCustomerId'];
            }

            if (!empty($subsequentOperationFields['rocketgate']['merchantInvoiceId'])
            ) {
                $merchantInvoiceId = $subsequentOperationFields['rocketgate']['merchantInvoiceId'];
            }
        }

        if ($merchantCustomerId != $command->merchantCustomerId()) {
            throw new InvalidMerchantInformationException('merchantCustomerId');
        }

        if ($merchantInvoiceId != $command->merchantInvoiceId()) {
            throw new InvalidMerchantInformationException('merchantInvoiceId');
        }
    }

    /**
     * @param PerformRocketgateCancelRebillCommand $command             PerformRocketgateCancelRebillCommand
     * @param Transaction                          $previousTransaction PreviousTransaction
     * @return RebillUpdateTransaction
     * @throws \Exception
     */
    protected function createRocketgateCancelRebillTransaction(
        PerformRocketgateCancelRebillCommand $command,
        Transaction $previousTransaction
    ): RebillUpdateTransaction {
        return RebillUpdateTransaction::createCancelRebillTransaction(
            $previousTransaction,
            RocketGateBillerSettings::ROCKETGATE,
            new RocketGateUpdateRebillBillerFields(
                $command->merchantId(),
                $command->merchantPassword(),
                $command->merchantCustomerId(),
                $command->merchantInvoiceId(),
                null
            )
        );
    }

    /**
     * @param PerformNetbillingCancelRebillCommand $command             Command
     * @param Transaction                          $previousTransaction Previous Transaction
     * @return RebillUpdateTransaction
     * @throws \Exception
     */
    protected function createNetbillingCancelRebillTransaction(
        PerformNetbillingCancelRebillCommand $command,
        Transaction $previousTransaction
    ): RebillUpdateTransaction {
        $billerMemberId = $this->getNetbillingBillerMemberIdFromTransaction($previousTransaction);

        return RebillUpdateTransaction::createNetbillingCancelRebillTransaction(
            $previousTransaction,
            NetbillingBillerSettings::NETBILLING,
            NetbillingRebillUpdateSettings::create(
                $command->siteTag(),
                $command->accountId(),
                $billerMemberId,
                $command->merchantPassword()
            )
        );
    }

    /**
     * @param PerformRocketgateUpdateRebillCommand $command             PerformRocketgateUpdateRebillCommand
     * @param Transaction                          $previousTransaction Previous Transaction
     * @return PaymentInformation
     * @throws Exception
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws \Exception
     */
    private function addPaymentInformation(
        PerformRocketgateUpdateRebillCommand $command,
        Transaction $previousTransaction
    ): PaymentInformation {
        if (empty($command->amount())) {
            $cardHash = $this->getAttribute($previousTransaction, 'cardHash');
            return PaymentTemplateInformation::create(
                RocketGateCardHash::create($cardHash)
            );
        }

        if (!empty($command->cardHash())) {
            return PaymentTemplateInformation::create(
                RocketGateCardHash::create($command->cardHash())
            );
        } else {
            return CreditCardInformation::create(
                true,
                CreditCardNumber::create($command->ccNumber()),
                null,
                null,
                $command->cvv(),
                $command->cardExpirationMonth(),
                $command->cardExpirationYear()
            );
        }
    }
}
