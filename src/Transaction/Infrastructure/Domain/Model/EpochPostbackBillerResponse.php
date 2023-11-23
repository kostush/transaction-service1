<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Epoch\Domain\Model\PostbackTranslateResponse;

class EpochPostbackBillerResponse extends EpochBillerResponse
{
    /** @var string */
    private $status;

    /** @var string */
    private $type;

    /** @var string */
    private $paymentType;

    /** @var string */
    private $paymentMethod;

    /**
     * @param int                $result          Result
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Request Payload
     * @param \DateTimeImmutable $requestDate     Request Date
     * @param string|null        $responsePayload Response Payload
     * @param \DateTimeImmutable $responseDate    Response Date
     */
    protected function __construct(
        int $result,
        string $code,
        string $reason,
        ?string $requestPayload,
        \DateTimeImmutable $requestDate,
        ?string $responsePayload,
        \DateTimeImmutable $responseDate
    ) {
        parent::__construct($result, $code, $reason, $requestPayload, $requestDate, $responsePayload, $responseDate);

        $response = json_decode($responsePayload, true);

        $this->status        = $response['status'] ?? '';
        $this->paymentType   = $response['paymentType'] ?? '';
        $this->paymentMethod = $response['paymentMethod'] ?? '';
        $this->type          = $response['type'] ?? '';
    }

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return EpochPostbackBillerResponse
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): EpochPostbackBillerResponse {
        $decodedResponse = json_decode($jsonResponse, true);

        $code   = '200';
        $reason = '';

        if (!empty($decodedResponse['code'])) {
            // is an error
            $code   = (string) $decodedResponse['code'];
            $reason = (string) $decodedResponse['reason'];
        }

        return new static(
            static::getStatus($decodedResponse),
            $code,
            $reason,
            null,
            $requestDate,
            $jsonResponse,
            $responseDate
        );
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function paymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param array $decodedResponse Decoded Response
     * @return int
     */
    protected static function getStatus(array $decodedResponse): int
    {
        switch ($decodedResponse['status'] ?? null) {
            case PostbackTranslateResponse::CHARGE_RESULT_APPROVED:
                $status = self::CHARGE_RESULT_APPROVED;
                break;

            case PostbackTranslateResponse::CHARGE_RESULT_DECLINED:
                $status = self::CHARGE_RESULT_DECLINED;
                break;

            default:
                $status = self::CHARGE_RESULT_ABORTED;
        }

        return $status;
    }

    /**
     * @return string|null
     */
    public function billerTransactionId(): ?string
    {
        $response = json_decode($this->responsePayload(), true);

        return $response['response']['transaction_id'] ?? null;
    }
}
