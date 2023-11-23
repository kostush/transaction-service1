<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

class NetbillingUpdateRebillNewCardTranslator implements NetbillingUpdateRebillTranslator
{

    use NetbillingPrepareBillerFieldsTrait;

    /**
     * @var NetbillingNewCreditCardChargeAdapter
     */
    protected $newCardChargeAdapter;

    /**
     * @var NetbillingUpdateRebillAdapter
     */
    protected $updateRebillAdapter;

    /**
     * NetbillingUpdateRebillNewCardTranslator constructor.
     * @param NetbillingNewCreditCardChargeAdapter $newCardChargeAdapter new card charge adapter
     * @param NetbillingUpdateRebillAdapter        $updateRebillAdapter  update membership adapter
     */
    public function __construct(
        NetbillingNewCreditCardChargeAdapter $newCardChargeAdapter,
        NetbillingUpdateRebillAdapter $updateRebillAdapter
    ) {
        $this->newCardChargeAdapter = $newCardChargeAdapter;
        $this->updateRebillAdapter  = $updateRebillAdapter;
    }

    /**
     * @param RebillUpdateTransaction $transaction rebill update transaction
     * @return NetbillingBillerResponse
     * @throws Exception
     * @throws NetbillingServiceException
     * @throws \Exception
     */
    public function update(RebillUpdateTransaction $transaction)
    {
        Log::info(
            'Preparing Netbilling update rebill request with new card - charge new credit card',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $netbillingNewCreditCardChargeCommand = $this->createNetbillingNewChargeCommand($transaction);
        $chargeResult                         = $this->newCardChargeAdapter->charge(
            $netbillingNewCreditCardChargeCommand,
            new DateTimeImmutable()
        );

        if ($chargeResult->result() !== NetbillingBillerResponse::CHARGE_RESULT_APPROVED) {
            return $chargeResult;
        }

        Log::info(
            'Preparing Netbilling update rebill request with new card - update member',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $templateTransId = $this->getTemplateTransIdForNewCC($chargeResult);

        $netbillingUpdateRebillCommand = $this->createForNewCCNetbillingUpdateRebillCommand(
            $transaction,
            $templateTransId
        );

        $updateRebillResult = $this->updateRebillAdapter->update(
            $netbillingUpdateRebillCommand,
            new DateTimeImmutable()
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
    protected function createNetbillingNewChargeCommand(
        RebillUpdateTransaction $transaction
    ): CreditCardChargeCommand {
        return new CreditCardChargeCommand(
            (string) $transaction->transactionId(),
            $this->prepareBillerFields($transaction),
            [ // membership fields
              'memberId' => $transaction->billerChargeSettings()->billerMemberId()
            ],
            $this->preparePaymentInformationFields($transaction),
            $this->prepareRecurringBillingFields($transaction),
            $this->prepareCustomerInformationFields($transaction)
        );
    }

    /**
     * @param NetbillingBillerResponse $chargeResult
     * @return int | null
     */
    public function getTemplateTransIdForNewCC(NetbillingBillerResponse $chargeResult): ?int
    {
        $templateTransId = null;

        if (!empty($chargeResult->responsePayload())) {
            $responsePayload = json_decode($chargeResult->responsePayload(), true);
            $templateTransId = $responsePayload['trans_id'] ?? null;
        }
        return (int) $templateTransId;
    }
}
