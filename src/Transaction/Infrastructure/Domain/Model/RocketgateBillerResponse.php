<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;

abstract class RocketgateBillerResponse extends BillerResponse
{
    public const PAYMENT_LINK_URL = 'PAYMENT_LINK_URL';

    /**
     * @var string
     */
    protected $billerTransactionId;

    /**
     * @var array
     */
    protected static $return400RocketgateCodes = ['406', '411', '414', '441', '448', '452'];

    /**
     * @param DateTimeImmutable $requestDate     Request date
     * @param string            $responsePayload Json Response
     * @param DateTimeImmutable $responseDate    Response date
     * @return RocketgateBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(
        DateTimeImmutable $requestDate,
        string $responsePayload,
        DateTimeImmutable $responseDate
    ) {
        $decodedResponse = json_decode($responsePayload);

        $responseStatus = self::getBillerResponseResultFrom(
            $decodedResponse,
            self::isThreeDUsed($decodedResponse)
        );

        if (!empty($decodedResponse->response)) {
            $code   = $decodedResponse->reason;
            $reason = RocketgateErrorCodes::getMessage((int) $decodedResponse->reason);
        } else {
            $code   = (string) $decodedResponse->code;
            $reason = $decodedResponse->reason;
        }

        return new static(
            $responseStatus,
            $code,
            $reason,
            !empty($decodedResponse->request) ? json_encode($decodedResponse->request) : null,
            $requestDate,
            !empty($decodedResponse->response) ? json_encode($decodedResponse->response) : null,
            $responseDate
        );
    }

    /**
     * @param Exception|null $exception Exception
     *
     * @return RocketgateBillerResponse
     * @throws Exception
     */
    public static function createAbortedResponse(?Exception $exception)
    {
        $code    = !empty($exception) ? $exception->getCode() : Code::ROCKETGATE_CIRCUIT_BREAKER_OPEN;
        $message = !empty($exception) ? $exception->getMessage() : Code::getMessage(
            Code::ROCKETGATE_CIRCUIT_BREAKER_OPEN
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
     * @param object $decodedResponse The json decoded response
     * @param bool   $isThreeDUsed    Is threeDS used
     *
     * @return int
     */
    protected static function getBillerResponseResultFrom(object $decodedResponse, bool $isThreeDUsed = false): int
    {
        $status = self::CHARGE_RESULT_DECLINED;

        if ($decodedResponse->code === '0') {
            if (property_exists($decodedResponse->response, RocketgateBillerResponse::PAYMENT_LINK_URL)) {
                $status = self::CHARGE_RESULT_PENDING;
            } else {
                $status = self::CHARGE_RESULT_APPROVED;
            }
        }

        if (RocketgateErrorCodes::isAbortedResponse((int) $decodedResponse->reason)) {
            $status = self::CHARGE_RESULT_ABORTED;
        }

        if ($isThreeDUsed && RocketgateErrorCodes::is3dsAuthRequired((int) $decodedResponse->reason)) {
            $status = self::CHARGE_RESULT_PENDING;
        }

        if ($isThreeDUsed && RocketgateErrorCodes::is3ds2InitRequired((int) $decodedResponse->reason)) {
            $status = self::CHARGE_RESULT_PENDING;
        }

        if ($isThreeDUsed && RocketgateErrorCodes::is3dsScaRequired((int) $decodedResponse->reason)) {
            $status = self::CHARGE_RESULT_PENDING;
        }

        return $status;
    }

    /**
     * @param object $decodedResponse Decoded response.
     * @return bool
     */
    protected static function isThreeDUsed(object $decodedResponse): bool
    {
        $request = property_exists($decodedResponse, 'request') ? $decodedResponse->request : null;

        if (!empty($request) && property_exists($request, 'use3DSecure')) {
            return filter_var($request->use3DSecure, FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function billerTransactionId(): ?string
    {
        $billerTransactionId = null;
        if ($this->responsePayload()) {
            $payload = json_decode($this->responsePayload());
            if (isset($payload->guidNo)) {
                $billerTransactionId = $payload->guidNo;
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
            if (isset($payload->balanceAmount)) {
                $balanceAmount = (float) $payload->balanceAmount;
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
            if (isset($payload->balanceCurrency)) {
                $balanceCurrency = $payload->balanceCurrency;
            }
        }

        return $balanceCurrency;
    }

    /**
     * @return bool
     */
    public function shouldReturn400(): bool
    {
        $shouldReturn400 = false;
        if (in_array($this->code(), self::$return400RocketgateCodes)) {
            $shouldReturn400 = true;
        }
        return $shouldReturn400;
    }

    /**
     * @return bool
     */
    public function shouldRetryWithoutThreeD(): bool
    {
        return RocketgateErrorCodes::isFailed3dsResponse((int) $this->code());
    }

    /**
     * @param bool $simplifiedThreeDSIsEnabled Feature flag
     * @return bool
     */
    public function shouldRetryWithThreeD(bool $simplifiedThreeDSIsEnabled = false): bool
    {
        if (null === $this->responsePayload()) {
            return false;
        }

        $responsePayload = json_decode(
            $this->responsePayload(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($this->retrieveThreeDSVersionFromPayload($responsePayload) === Transaction::THREE_DS_TWO
            || RocketgateErrorCodes::is3ds2InitRequired((int) $this->code())
        ) {
            return !isset(
                $responsePayload['_3DSECURE_DEVICE_COLLECTION_JWT'],
                $responsePayload['_3DSECURE_DEVICE_COLLECTION_URL']
            );
        }

        if (RocketgateErrorCodes::is3dsAuthRequired((int) $this->code())) {
            return !isset($responsePayload['PAREQ'], $responsePayload['acsURL']);
        }

        if (RocketgateErrorCodes::is3dsScaRequired((int) $this->code())) {
            // If RocketGate responds with code 228 then we`ll trigger retry with 3DS,
            // unless we are using a payment template and the simplified 3DS feature is disabled
            if (!$simplifiedThreeDSIsEnabled && $this->isSecRev($this->requestPayload())) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNsfTransaction(): bool
    {
        return RocketgateErrorCodes::RG_CODE_DECLINED_OVER_LIMIT === (int) $this->code();
    }

    /**
     * @return bool
     */
    public function threeDsAuthIsRequired(): bool
    {
        return RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED === (int) $this->code();
    }

    /**
     * @return bool
     */
    public function threeDsInitIsRequired(): bool
    {
        return RocketgateErrorCodes::RG_CODE_3DS2_INITIATION === (int) $this->code();
    }

    /**
     * @return bool
     */
    public function threeDsScaIsRequired(): bool
    {
        return RocketgateErrorCodes::RG_CODE_3DS_SCA_REQUIRED === (int) $this->code();
    }

    /**
     * @return int|null
     */
    public function threedsVersion(): ?int
    {
        if (empty($this->responsePayload())) {
            return null;
        }

        $responsePayload = json_decode(
            $this->responsePayload(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $threeDSVersionFromPayload = $this->retrieveThreeDSVersionFromPayload($responsePayload);

        if (null !== $threeDSVersionFromPayload) {
            return $threeDSVersionFromPayload;
        }

        if (isset($responsePayload['acsURL'], $responsePayload['PAREQ'])) {
            return Transaction::THREE_DS_ONE;
        }

        if (isset($responsePayload['_3DSECURE_DEVICE_COLLECTION_JWT'], $responsePayload['_3DSECURE_DEVICE_COLLECTION_URL'])) {
            return Transaction::THREE_DS_TWO;
        }

        if (isset($responsePayload['_3DSECURE_STEP_UP_URL'], $responsePayload['_3DSECURE_STEP_UP_JWT'], $responsePayload['guidNo'])) {
            return Transaction::THREE_DS_TWO;
        }

        return null;
    }

    /**
     * @param array $responsePayload Three DS Version from Rocketgate
     * @return int|null
     */
    private function retrieveThreeDSVersionFromPayload(array $responsePayload): ?int
    {
        if (!isset($responsePayload['_3DSECURE_VERSION'])) {
            return null;
        }

        [$threeDSVersion] = explode('.', $responsePayload['_3DSECURE_VERSION']);

        if (!in_array($threeDSVersion, [Transaction::THREE_DS_ONE, Transaction::THREE_DS_TWO])) {
            return null;
        }

        return (int) $threeDSVersion;
    }

    /**
     * @param string $request Response
     * @return bool
     */
    private function isSecRev(string $request): bool
    {
        $request = json_decode($request, true, 512, JSON_THROW_ON_ERROR);

        return !empty($request['cardHash']);
    }
}
