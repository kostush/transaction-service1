<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Logger\Log;
use ProBillerNG\Pumapay\Code as PumapayCode;
use ProBillerNG\Pumapay\Domain\Model\GenerateQrCodeResponse;

class PumapayRetrieveQrCodeBillerResponse extends PumapayBillerResponse
{
    /**
     * @var string
     */
    private $qrCode;

    /**
     * @var string
     */
    private $encryptText;

    /**
     * @var array
     */
    protected static $return400PumapayCodes = [PumapayCode::PUMAPAY_BAD_REQUEST];

    /**
     * PumapayRetrieveQrCodeBillerResponse constructor.
     * @param int                $result          Result
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Request payload
     * @param \DateTimeImmutable $requestDate     Request date
     * @param string|null        $responsePayload Response payload
     * @param \DateTimeImmutable $responseDate    Response date
     * @throws \ProBillerNG\Logger\Exception
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

        Log::info('Biller response from Pumapay Service', ['response' => $response]);

        // We fall back to empty string instead of throwing an exception because the flow would stop and the intention
        // is to create an aborted transaction.
        $this->qrCode      = $response['data']['qrImage'] ?? '';
        $this->encryptText = $response['data']['encryptText'] ?? '';
    }

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return PumapayBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): PumapayBillerResponse {
        $decodedResponse = json_decode($jsonResponse, true);

        return new static(
            static::getStatus($decodedResponse),
            $decodedResponse['code'],
            (string) $decodedResponse['reason'],
            json_encode($decodedResponse['request']),
            $requestDate,
            json_encode($decodedResponse['response']),
            $responseDate
        );
    }

    /**
     * @return string
     */
    public function qrCode(): string
    {
        return $this->qrCode;
    }

    /**
     * @return string
     */
    public function encryptText(): string
    {
        return $this->encryptText;
    }

    /**
     * @param array $decodedResponse Decoded Response
     * @return int
     */
    protected static function getStatus(array $decodedResponse): int
    {
        $status = self::CHARGE_RESULT_PENDING;

        $success = $decodedResponse['response']['success'] ?? false;
        $code    = $decodedResponse['code'] ?? 0;

        if ($success !== true && (int) $code !== GenerateQrCodeResponse::RESPONSE_CODE) {
            $status = self::CHARGE_RESULT_ABORTED;
        }

        return $status;
    }
}
