<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\PumapayAdapter;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;

class PumapayTranslatingService implements PumapayService
{
    /**
     * @var PumapayAdapter|PumapayRetrieveQrCodeAdapter
     */
    protected $qrCodeAdapter;

    /**
     * @var PumapayAdapter|PumapayPostbackAdapter
     */
    protected $postbackAdapter;

    /**
     * @var PumapayAdapter|PumapayCancelRebillAdapter
     */
    protected $cancelRebillAdapter;

    /**
     * PumapayTranslatingService constructor.
     * @param PumapayRetrieveQrCodeAdapter $qrCodeAdapter       Pumapay Retrieve QrCode Adapter
     * @param PumapayPostbackAdapter       $postbackAdapter     Pumapay Postback Adapter
     * @param PumapayCancelRebillAdapter   $cancelRebillAdapter Pumapay Cancel Rebill Adapter
     */
    public function __construct(
        PumapayRetrieveQrCodeAdapter $qrCodeAdapter,
        PumapayPostbackAdapter $postbackAdapter,
        PumapayCancelRebillAdapter $cancelRebillAdapter
    ) {
        $this->qrCodeAdapter       = $qrCodeAdapter;
        $this->postbackAdapter     = $postbackAdapter;
        $this->cancelRebillAdapter = $cancelRebillAdapter;
    }

    /**
     * @param ChargeTransaction $transaction
     * @return PumapayBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function retrieveQrCode(ChargeTransaction $transaction): PumapayBillerResponse
    {
        Log::info(
            'Preparing Pumapay retrieve QR code request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        return $this->qrCodeAdapter->retrieveQrCode($transaction);
    }

    /**
     * @param string $payload         Payload
     * @param string $transactionType Transaction Type
     * @return PumapayPostbackBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function translatePostback(string $payload, string $transactionType): PumapayPostbackBillerResponse
    {
        return $this->postbackAdapter->getTranslatedPostback($payload, $transactionType);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $businessId  Business Id
     * @param string            $apiKey      Api Key
     * @return PumapayCancelRebillBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function cancelRebill(
        ChargeTransaction $transaction,
        string $businessId,
        string $apiKey
    ): PumapayCancelRebillBillerResponse {
        return $this->cancelRebillAdapter->cancelRebill($transaction, $businessId, $apiKey);
    }
}
