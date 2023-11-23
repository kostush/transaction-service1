<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use ProBillerNG\BI\Event\BaseEvent as BiEvent;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\BillerResponse\Netbilling;
use ProBillerNG\Transaction\Application\BI\BillerResponse\Rocketgate;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Status;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;

/**
 * Class BaseEvent
 * @package ProBillerNG\Transaction\Application\BI
 */
abstract class BaseEvent extends BiEvent
{
    public const NO_BIN_ROUTING           = 'None';
    public const NO_ACTION                = 'None';
    public const PAYMENT_TYPE             = 'None';
    public const NO_BILLER_TRANSACTION_ID = 'None';
    public const BI_CREDIT_CARD_TYPE      = 'CreditCard';

    //transaction status
    private $statusMapping = [
        Approved::class => 'TransactionApproved',
        Aborted::class  => 'TransactionAborted',
        Declined::class => 'TransactionDeclined',
        Pending::class  => 'TransactionPending',
    ];

    /**
     * @param Status $transactionStatus transaction status
     * @throws Exception
     * @return string
     */
    protected function getBiStatusFrom(Status $transactionStatus): string
    {
        $biStatus = Arr::get($this->statusMapping, get_class($transactionStatus));

        if (empty($biStatus)) {
            $biStatus = (string) $transactionStatus;
            Log::alert('Transaction status not found!', ['transactionStatus' => $transactionStatus]);
        }

        return $biStatus;
    }

    /**
     * @param string $paymentType payment type
     * @throws Exception
     * @return string
     */
    protected function getBiPaymentTypeFrom(?string $paymentType): string
    {
        if (empty($paymentType)) {
            return self::PAYMENT_TYPE;
        }

        $biStatus = Arr::get(PaymentType::biPaymentTypeMapping(), $paymentType);

        if (empty($biStatus)) {
            $biStatus = (string) $paymentType;
            Log::alert('Payment type not found!', ['paymentType' => $paymentType]);
        }

        return $biStatus;
    }

    /**
     * @param PaymentInformation $paymentInformation The Payment template information object
     * @return string
     */
    protected function getBiPaymentTemplateUsageFrom(?PaymentInformation $paymentInformation): string
    {
        return $paymentInformation instanceof PaymentTemplateInformation
               || $paymentInformation instanceof NetbillingPaymentTemplateInformation ? 'YES' : 'NO';
    }

    /**
     * @param BillerResponse|null $billerResponse Biller response
     * @return string|null
     */
    protected function billerResponseDate(?BillerResponse $billerResponse): ?string
    {
        if (!$billerResponse) {
            return null;
        }

        $responseDate = $billerResponse->responseDate();

        if (!$responseDate) {
            return null;
        }

        return $responseDate->format('Y-m-d H:i:s');
    }

    /**
     * @param BillerResponse|null $billerResponse Biller response
     * @return string|null
     */
    protected function billerTransactionId(?BillerResponse $billerResponse): ?string
    {
        $billerTransactionId = self::NO_BILLER_TRANSACTION_ID;

        if ($billerResponse instanceof RocketgateBillerResponse
            || $billerResponse instanceof PumapayPostbackBillerResponse
            || $billerResponse instanceof NetbillingBillerResponse
            || $billerResponse instanceof QyssoBillerResponse
        ) {
            $billerTransactionId = $billerResponse->billerTransactionId();
        }

        return $billerTransactionId;
    }

    /**
     * @param string              $version         Event version
     * @param Transaction         $transaction     Transaction Info
     * @param BillerResponse|null $billerResponse  Biller response
     * @param string              $billerName      Biller name
     * @param string              $transactionType Transaction type: charge, rebill
     * @param string|null         $action          Action
     *
     * @return array
     *
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createBaseEventValue(
        string $version,
        Transaction $transaction,
        ?BillerResponse $billerResponse,
        string $billerName,
        string $transactionType,
        string $action = null
    ): array {
        $baseEventValue = [
            'version'               => $version,
            'timestamp'             => Carbon::now()->format('Y-m-d H:i:s'),
            'sessionId'             => Log::getSessionId(),
            'transactionId'         => (string) $transaction->transactionId(),
            'billerTransactionId'   => $this->billerTransactionId($billerResponse),
            'billerResponseDate'    => $this->billerResponseDate($billerResponse),
            'biller'                => ucfirst($billerName),
            'transactionState'      => $this->getBiStatusFrom($transaction->status()),
            'paymentType'           => $this->getBiPaymentTypeFrom($transaction->paymentType()),
            'paymentMethod'         => $transaction->paymentMethod(),
            'paymentTemplate'       => $this->getBiPaymentTemplateUsageFrom($transaction->paymentInformation()),
            // 0 = sale (paid)
            // 1 = auth (free)
            'freeSale'              => (int) $transaction->isFreeSale(),
            'binRouting'            => self::NO_BIN_ROUTING,
            'transactionType'       => $transactionType,
            'action'                => $action ?? self::NO_ACTION,
            'previousTransactionId' => $this->getPreviewsTransaction($transaction, $transactionType),
            'requiredToUse3D'       => $transaction->requiredToUse3D(),
            'transactionWith3D'     => $transaction->with3D(),
            'threedsVersion'        => $transaction->threedsVersion(),
            'siteId'                => (string) ($transaction->siteId() ?? $transaction->previousTransaction()->siteId()),
        ];

        $baseEventValue['billerResponse'] = $billerResponse
            ? $this->getBillerResponseFields($billerResponse->responsePayload()) : '';

        if ($transaction->billerChargeSettings() instanceof RocketGateBillerSettings) {
            $baseEventValue = array_merge(
                $baseEventValue,
                Rocketgate::getSpecificBillerFields($transaction, $billerResponse)
            );
        }

        if ($transaction->billerChargeSettings() instanceof NetbillingBillerSettings) {
            $baseEventValue = array_merge(
                $baseEventValue,
                Netbilling::getSpecificBillerFields($transaction)
            );
        }

        // add declined transaction's reason code if any given. For aborted transactions we might not have it.
        if (!empty($billerResponse)) {
            if ($billerResponse->declined()) {
                $baseEventValue['reasonCodeDecline'] = $billerResponse->code();
            }
        }

        return $baseEventValue;
    }

    /**
     * @param string|null $responsePayload Response Payload
     *
     * @return array|mixed
     */
    private function getBillerResponseFields(?string $responsePayload): array
    {
        if (empty($responsePayload)) {
            return [];
        }

        // make sure we handle json_decode eventual errors
        $billerResponse = json_decode($responsePayload, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $billerResponse;
        }

        return [];
    }

    /**
     * @param Transaction $transaction     Transaction
     * @param string      $transactionType Transaction type: charge, rebill
     *
     * @return string|null
     */
    private function getPreviewsTransaction(Transaction $transaction, string $transactionType): ?string
    {
        if ($transactionType === RebillUpdateTransactionCreated::TRANSACTION_TYPE) {
            return (string) $transaction->previousTransactionId();
        }

        return null;
    }
}
