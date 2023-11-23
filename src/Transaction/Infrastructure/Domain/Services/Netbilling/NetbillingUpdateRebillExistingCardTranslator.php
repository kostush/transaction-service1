<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Logger\Exception as LoggerExceptionAlias;
use ProBillerNG\Logger\Log;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\UpdateRebillAdapter;

class NetbillingUpdateRebillExistingCardTranslator implements NetbillingUpdateRebillTranslator
{
    use NetbillingPrepareBillerFieldsTrait;

    /**
     * @var UpdateRebillAdapter
     */
    protected $updateRebillAdapter;

    /**
     * @var NetbillingExistingCreditCardChargeAdapter
     */
    protected $existingCardChargeAdapter;

    /**
     * NetbillingUpdateRebillExistingCardTranslator constructor
     * @param NetbillingExistingCreditCardChargeAdapter $existingCardChargeAdapter existing card charge adapter
     * @param UpdateRebillNetbillingAdapter             $updateRebillAdapter       updateRebill adapter
     */
    public function __construct(
        NetbillingExistingCreditCardChargeAdapter $existingCardChargeAdapter,
        UpdateRebillNetbillingAdapter $updateRebillAdapter
    ) {
        $this->existingCardChargeAdapter = $existingCardChargeAdapter;
        $this->updateRebillAdapter       = $updateRebillAdapter;
    }

    /**
     * @param RebillUpdateTransaction $transaction rebillUpdate transaction
     * @return NetbillingBillerResponse
     * @throws LoggerExceptionAlias
     * @throws \Exception
     */
    public function update(RebillUpdateTransaction $transaction)
    {
        Log::info(
            'Preparing Netbilling update rebill request with existing card - charge existing card',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingExistingCardChargeCommand = $this->createNetbillingExistingCardChargeCommand($transaction);
        $chargeResult =  $this->existingCardChargeAdapter->charge(
            $netbillingExistingCardChargeCommand,
            new \DateTimeImmutable()
        );

        if ($chargeResult->result() !== NetbillingBillerResponse::CHARGE_RESULT_APPROVED
        ) {
            return $chargeResult;
        }

        Log::info(
            'Preparing Netbilling update rebill request with existing card - update member',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingRebillUpdateCommand = $this->createNetbillingUpdateRebillCommand($transaction);
        $updateRebillResult            = $this->updateRebillAdapter->update(
            $netbillingRebillUpdateCommand,
            new \DateTimeImmutable()
        );

        if ($updateRebillResult->result() !== NetbillingBillerResponse::CHARGE_RESULT_APPROVED
            && $updateRebillResult->reason() !== 'No changes made'
        ) {
            return $updateRebillResult;
        }

        return $chargeResult;
    }

    /**
     * @param RebillUpdateTransaction $transaction rebill transaction
     * @return CreditCardChargeCommand
     * @throws \Exception
     */
    protected function createNetbillingExistingCardChargeCommand(
        RebillUpdateTransaction $transaction
    ): CreditCardChargeCommand {
        return new CreditCardChargeCommand(
            (string) $transaction->transactionId(),
            $this->prepareBillerFields($transaction),
            [ // membership fields
              'memberId' => $transaction->billerChargeSettings()->billerMemberId()
            ],
            $this->prepareExistingPaymentInformationFields($transaction),
            $this->prepareRecurringBillingFields($transaction),
            [
                'ipAddress' => '',
                'host'      => '',
                'browser'   => ''
            ]
        );
    }

}