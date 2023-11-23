<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;

class PumapayPostbackBillerResponse extends PumapayBillerResponse
{
    /** @var string */
    private $status;

    /** @var string */
    private $type;

    /**
     * PumapayPostbackBillerResponse constructor.
     * @param int                $result
     * @param string             $code
     * @param string             $reason
     * @param string|null        $requestPayload
     * @param \DateTimeImmutable $requestDate
     * @param string|null        $responsePayload
     * @param \DateTimeImmutable $responseDate
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

        $this->status = $response['status'] ?? '';
        $this->type   = $response['type'] ?? '';
    }

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return PumapayPostbackBillerResponse
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): PumapayPostbackBillerResponse {
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
            case PostbackResponse::CHARGE_RESULT_APPROVED:
                $status = self::CHARGE_RESULT_APPROVED;
                break;

            case PostbackResponse::CHARGE_RESULT_DECLINED:
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

        return $response['response']['transactionData']['id'] ?? null;
    }
}
