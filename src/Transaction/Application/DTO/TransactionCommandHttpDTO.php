<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Transaction\Application\Services\PrepaidInfoExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;

/**
 * Class TransactionCommandHttpDTO
 * @package ProBillerNG\Transaction\Application\DTO
 */
class TransactionCommandHttpDTO implements \JsonSerializable
{
    use PrepaidInfoExtractorTrait;

    /**
     * @var array
     */
    private $transactionOutput;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var ErrorClassification|null
     */
    private $errorClassification;

    /**
     * TransactionHttpDTO constructor.
     *
     * @param Transaction              $transaction         Transaction
     * @param ErrorClassification|null $errorClassification Error Classification for declined transaction
     */
    public function __construct(Transaction $transaction, ?ErrorClassification $errorClassification = null)
    {
        $this->transaction         = $transaction;
        $this->errorClassification = $errorClassification;

        $this->initTransaction($transaction);
        $this->initPrepaidInfo($transaction);
    }

    /**
     * @param Transaction $transaction Transaction
     *
     * @return void
     */
    private function initTransaction(Transaction $transaction): void
    {
        $this->transactionOutput['transactionId'] = (string) $transaction->transactionId();
        $this->transactionOutput['status']        = (string) $transaction->status();

        // 3ds1 or 3ds2 flow
        if ($transaction->with3D()) {
            // we need to add the threeD version regardless of the transaction status
            if (!empty($transaction->threedsVersion())) {
                $this->transactionOutput['threeD']['version'] = $transaction->threedsVersion();
            }

            if ($transaction->status()->pending()) {
                switch ($transaction->threedsVersion()) {
                    case Transaction::THREE_DS_ONE:
                        //TODO remove these 2 parameters once the threeD object is consumed by PGW
                        $this->transactionOutput['pareq'] = $this->getAttribute($transaction, 'PAREQ'); // deprecated
                        $this->transactionOutput['acs']   = $this->getAttribute($transaction, 'acsURL');  // deprecated

                        $this->transactionOutput['threeD']['pareq'] = $this->getAttribute($transaction, 'PAREQ');
                        $this->transactionOutput['threeD']['acs']   = $this->getAttribute($transaction, 'acsURL');

                        return;
                    case Transaction::THREE_DS_TWO:
                        $stepUpUrl = $this->getAttribute($transaction, '_3DSECURE_STEP_UP_URL');
                        $stepUpJwt = $this->getAttribute($transaction, '_3DSECURE_STEP_UP_JWT');
                        $md        = $this->getAttribute($transaction, 'guidNo');

                        $deviceUrl = $this->getAttribute($transaction, '_3DSECURE_DEVICE_COLLECTION_URL');
                        $deviceJwt = $this->getAttribute($transaction, '_3DSECURE_DEVICE_COLLECTION_JWT');

                        if ($deviceJwt !== null && $deviceUrl !== null) {
                            $this->transactionOutput['threeD']['deviceCollectionUrl'] = $deviceUrl;
                            $this->transactionOutput['threeD']['deviceCollectionJWT'] = $deviceJwt;
                        }

                        if ($stepUpUrl !== null && $stepUpJwt !== null && $md !== null) {
                            $this->transactionOutput['threeD']['stepUpUrl'] = $stepUpUrl;
                            $this->transactionOutput['threeD']['stepUpJwt'] = $stepUpJwt;
                            $this->transactionOutput['threeD']['md']        = $md;
                        }

                        return;
                }

                $paymentLinkUrl = $this->getAttribute(
                    $transaction,
                    RocketgateBillerResponse::PAYMENT_LINK_URL
                );

                if (!empty($paymentLinkUrl)) {
                    $this->transactionOutput['threeD']['paymentLinkUrl'] = $paymentLinkUrl;

                    return;
                }
            }
        }

        if (!$transaction->status()->approved()) {
            $this->transactionOutput['code']   = $transaction->code();
            $this->transactionOutput['reason'] = $transaction->reason();

            // Not all billers have errorClassification
            if (!empty($this->errorClassification)) {
                $this->transactionOutput = array_merge(
                    $this->transactionOutput,
                    $this->errorClassification->toArray()
                );
            }
        }
    }

    /**
     * @param Transaction $transaction Transaction Instance.
     *
     * @return void
     */
    private function initPrepaidInfo(Transaction $transaction): void
    {
        $prepaidInfoType = $this->prepaidInfoType($transaction);
        if ($prepaidInfoType->isAvailable()) {
            $this->transactionOutput['prepaid'] = $prepaidInfoType->toArray();
        }
    }

    /**
     * @return bool
     */
    public function shouldReturn400(): bool
    {
        return $this->transaction->shouldReturn400();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->transactionOutput;
    }
}
