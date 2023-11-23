<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use Exception;
use DateTimeImmutable;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;

class NetbillingBillerResponse extends BillerResponse
{
    const CHARGE_RESULT_APPROVED = 1;
    const CHARGE_RESULT_DECLINED = 2;
    const CHARGE_RESULT_ABORTED  = 3;
    const NSF_ERROR_CODE         = 105;

    /**
     * @return bool
     */
    public function approved(): bool
    {
        return $this->result() === self::CHARGE_RESULT_APPROVED;
    }

    /**
     * @return bool
     */
    public function declined(): bool
    {
        return $this->result() === self::CHARGE_RESULT_DECLINED;
    }

    /**
     * @return bool
     */
    public function aborted(): bool
    {
        return $this->result() === self::CHARGE_RESULT_ABORTED;
    }

    /**
     * @return bool
     */
    public function shouldReturn400()
    {
        $shouldReturn400 = false;
        if (!$this->approved()) {
            $shouldReturn400 = true;
        }
        return $shouldReturn400;
    }

    /**
     * @param object $decodedResponse The json decoded response
     *
     * @return int
     */
    protected static function getBillerResponseResultFrom(object $decodedResponse): int
    {
        $status = self::CHARGE_RESULT_DECLINED;

        if ($decodedResponse->code == '0') {
            $status = self::CHARGE_RESULT_APPROVED;
        }

        if ($decodedResponse->code == '9000') {
            $status = self::CHARGE_RESULT_ABORTED;
        }

        return $status;
    }

    /**
     * @return string|null
     */
    public function billerTransactionId(): ?string
    {
        $billerTransactionId = null;
        if ($this->responsePayload()) {
            $payload = json_decode($this->responsePayload());
            if (isset($payload->trans_id)) {
                $billerTransactionId = $payload->trans_id;
            }
        }

        return $billerTransactionId;
    }

    /**
     * @return float|null
     */
    public function balanceAmount(): ?float
    {
        $balanceAmount = null;
        if ($this->responsePayload()) {
            $payload = json_decode($this->responsePayload());
            if (isset($payload->settle_amount)) {
                $balanceAmount = (float) $payload->settle_amount;
            }
        }

        return $balanceAmount;
    }

    /**
     * @return string|null
     */
    public function balanceCurrency(): ?string
    {
        $balanceCurrency = null;
        if ($this->responsePayload()) {
            $payload = json_decode($this->responsePayload());
            if (isset($payload->settle_currency)) {
                $balanceCurrency = $payload->settle_currency;
            }
        }

        return $balanceCurrency;
    }

    /**
     * @param DateTimeImmutable $requestDate     Request date
     * @param string             $responsePayload Json Response
     * @param DateTimeImmutable $responseDate    Response date
     * @return NetbillingBillerResponse
     */
    public static function create(
        DateTimeImmutable $requestDate,
        string $responsePayload,
        DateTimeImmutable $responseDate
    ) {
        $decodedResponse = json_decode($responsePayload);

        $responseStatus = self::getBillerResponseResultFrom($decodedResponse);

        $response = $decodedResponse->response;
        if (!empty($response)) {
            $response = json_encode($response);
        } else {
            $response = null;
        }

        $code = $decodedResponse->code;

        return new static(
            $responseStatus,
            (string) $code,
            $decodedResponse->reason,
            json_encode($decodedResponse->request),
            $requestDate,
            $response,
            $responseDate
        );
    }

    /**
     * @param Exception|null $exception
     * @return NetbillingBillerResponse
     * @throws Exception
     */
    public static function createAbortedResponse(?Exception $exception)
    {
        $code    = !empty($exception) ? $exception->getCode() : Code::NETBILLING_CIRCUIT_BREAKER_OPEN;
        $message = !empty($exception) ? $exception->getMessage() : Code::getMessage(
            Code::NETBILLING_CIRCUIT_BREAKER_OPEN
        );

        return new static(
            self::CHARGE_RESULT_ABORTED,
            (string) $code,
            $message,
            null,
            new DateTimeImmutable(),
            null,
            new DateTimeImmutable()
        );
    }

    /**
     * @return bool
     */
    public function isNsfTransaction(): bool
    {
        return $this->code() == self::NSF_ERROR_CODE;
    }
}
