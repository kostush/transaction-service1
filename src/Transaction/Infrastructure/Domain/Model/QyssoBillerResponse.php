<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;

abstract class QyssoBillerResponse extends BillerResponse
{
    const STATUS_TRANSACTION_APPROVED = "000";
    const STATUS_WAITING_FOR_REDIRECT = "553";

    /**
     * @param string $status
     * @return int
     */
    protected static function mapStatus(string $status): int
    {
        switch ($status ?? null) {
            case self::STATUS_WAITING_FOR_REDIRECT:
                return self::CHARGE_RESULT_PENDING;
                break;

            case self::STATUS_TRANSACTION_APPROVED:
                return self::CHARGE_RESULT_APPROVED;
                break;

            default:
                return self::CHARGE_RESULT_DECLINED;
        }
    }

    /**
     * Assembling billerTransactionId for DWS from transaction id and reply code
     * @return string|null billerTransactionId
     */
    public function billerTransactionId(): ?string
    {
        $billerTransactionId = null;

        if ($this->responsePayload()) {
            $payload = json_decode($this->responsePayload());

            if (isset($payload->Reply) && isset($payload->TransID)) {
                $billerTransactionId = $payload->TransID . '-' . $this->mapBillerTransactionIdBIType($payload->Reply);
            } elseif (isset($payload->reply_code) && isset($payload->trans_id)) {
                $billerTransactionId = $payload->trans_id . '-' . $this->mapBillerTransactionIdBIType($payload->reply_code);
            }
        }

        return $billerTransactionId;
    }

    /**
     * Mapping reply code to status
     * @param string $replyCode Reply code from biller
     * @return int
     */
    private function mapBillerTransactionIdBIType(string $replyCode) : int
    {
        switch ($replyCode) {
            case self::STATUS_WAITING_FOR_REDIRECT:
                return 4;

            case self::STATUS_TRANSACTION_APPROVED:
                return 1;

            default: // Declined
                return 2;
        }
    }
}
