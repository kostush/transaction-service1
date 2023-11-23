<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\UpdateRebillAdapter;

class RocketgateUpdateRebillTranslator
{
    /**
     * @var UpdateRebillAdapter
     */
    protected $updateRebillAdapter;

    /**
     * RocketgateUpdateRebillTranslationService constructor.
     * @param UpdateRebillAdapter $updateRebillAdapter Update Rebill Adapter
     */
    public function __construct(
        UpdateRebillAdapter $updateRebillAdapter
    ) {
        $this->updateRebillAdapter = $updateRebillAdapter;
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return \ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function start(RebillUpdateTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate start rebill request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateUpdateRebillCommand = $this->createRocketgateUpdateRebillCommand($transaction, true);

        return $this->updateRebillAdapter->start($rocketgateUpdateRebillCommand, new \DateTimeImmutable());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return \ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function stop(RebillUpdateTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate stop rebill request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateUpdateRebillCommand = $this->createRocketgateUpdateRebillCommand($transaction);

        return $this->updateRebillAdapter->stop($rocketgateUpdateRebillCommand, new \DateTimeImmutable());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return \ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function update(RebillUpdateTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate update rebill request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateUpdateRebillCommand = $this->createRocketgateUpdateRebillCommand($transaction, true);

        return $this->updateRebillAdapter->update($rocketgateUpdateRebillCommand, new \DateTimeImmutable());
    }

    /**
     * @param RebillUpdateTransaction $transaction          Rebill Update Transaction
     * @param bool                    $setMerchantInitiated Merchant initiated must be set or not
     * @return UpdateRebillCommand
     */
    protected function createRocketgateUpdateRebillCommand(
        RebillUpdateTransaction $transaction,
        bool $setMerchantInitiated = false
    ): UpdateRebillCommand {

        if ($transaction->paymentInformation() instanceof PaymentTemplateInformation) {
            $paymentFields['cardHash'] = (string) $transaction->paymentInformation()->rocketGateCardHash();
        } else {
            $paymentFields['number']          = $transaction->paymentInformation()->creditCardNumber()->cardNumber();
            $paymentFields['expirationMonth'] = $transaction->paymentInformation()->expirationMonth();
            $paymentFields['expirationYear']  = $transaction->paymentInformation()->expirationYear();
            $paymentFields['cvv']             = $transaction->paymentInformation()->cvv();
            $setMerchantInitiated             = false;
        }
        $paymentFields['amount']   = $transaction->chargeInformation()->amount()->value();
        $paymentFields['currency'] = (string) $transaction->chargeInformation()->currency();

        $rebillFields = [];
        $rebill       = $transaction->chargeInformation()->rebill();
        if ($rebill instanceof Rebill) {
            $rebillFields           = $rebill->toArray();
            $rebillFields['amount'] = $rebillFields['amount']['value'];
        }

        return new UpdateRebillCommand(
            (string) $transaction->transactionId(),
            $this->prepareBillerFields($transaction, $setMerchantInitiated),
            $rebillFields,
            $paymentFields,
            env("BILLER_ROCKETGATE_TEST_MODE") ?? true
        );
    }

    /**
     * Get the mandatory fields for the rocketgate request
     * @param RebillUpdateTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareBillerFields(RebillUpdateTransaction $transaction, bool $setMerchantInitiated = false): array
    {
        $billerFields = [
            'merchantId'         => $transaction->billerChargeSettings()->merchantId(),
            'merchantPassword'   => $transaction->billerChargeSettings()->merchantPassword(),
            'merchantCustomerId' => $transaction->billerChargeSettings()->merchantCustomerId(),
            'merchantInvoiceId'  => $transaction->billerChargeSettings()->merchantInvoiceId(),
            'merchantAccount'    => $transaction->billerChargeSettings()->merchantAccount()
        ];

        if ($setMerchantInitiated) {
            $billerFields['isMerchantInitiated'] = Paysites::checkIfPaysites((string) $transaction->previousTransaction()->siteId());
        }

        return $billerFields;
    }
}
