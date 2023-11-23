<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Rebill;

class LegacyPostbackBillerResponse extends LegacyBillerResponse
{
    const APPROVED_STATUS_CODE = 0;

    const STATUS_APPROVED = 'approved';

    const STATUS_DECLINED = 'declined';

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isCrossSale;

    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * @var integer
     */
    private $legacyTransactionId;

    /**
     * @var integer
     */
    private $legacyMemberId;

    /**
     * @var integer
     */
    private $legacySubscriptionId;

    /**
     * @var Rebill|null
     */
    private $rebill;

    /**
     * LegacyNewSaleBillerResponse constructor.
     * @param int               $status          Status
     * @param string            $code            Code
     * @param string            $reason          Reason
     * @param string|null       $requestPayload  Request Payload
     * @param DateTimeImmutable $requestDate     Request Date
     * @param array             $responsePayload Response Payload
     * @param DateTimeImmutable $responseDate    Response Date
     * @param string            $type            Type
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    private function __construct(
        int $status,
        string $code,
        string $reason,
        ?string $requestPayload,
        DateTimeImmutable $requestDate,
        array $responsePayload,
        DateTimeImmutable $responseDate,
        string $type
    ) {
        parent::__construct(
            $status,
            $code,
            $reason,
            $requestPayload,
            $requestDate,
            json_encode($responsePayload),
            $responseDate
        );
        $this->type                 = $type;
        $this->legacySubscriptionId = $this->getLegacySubscriptionIdFromPayload($responsePayload);
        $this->legacyTransactionId  = $this->getLegacyTransactionIdFromPayload($responsePayload);
        $this->legacyMemberId       = $this->getLegacyMemberIdFromPayload($responsePayload);
        $this->amount               = $this->getAmountFromPayload($responsePayload);
        $this->isCrossSale          = $this->checkIfIsCrossSale($responsePayload);
        $this->rebill               = Rebill::createRebillFromLegacyResponsePayload(
            $this->getSubscriptionArrayFromPayload($responsePayload)
        );
    }

    /**
     * @param array $payload
     * @return string
     */
    private function getLegacySubscriptionIdFromPayload(array $payload): ?string
    {
        if (!isset($payload['data']['subscriptionId'])) {
            return null;
        }

        return (string) $payload['data']['subscriptionId'];
    }

    /**
     * @param array $payload
     * @return string
     */
    private function getLegacyTransactionIdFromPayload(array $payload): ?string
    {
        if (!isset($payload['data']['transactionId'])) {
            return null;
        }

        return (string) $payload['data']['transactionId'];
    }

    /**
     * @param array $payload
     * @return string
     */
    private function getLegacyMemberIdFromPayload(array $payload): ?string
    {
        if (!isset($payload['data']['memberDetails']['member_id'])) {
            return null;
        }

        return (string) $payload['data']['memberDetails']['member_id'];
    }

    /**
     * @param array $payload Response Payload
     * @return Amount|null
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function getAmountFromPayload(array $payload): ?Amount
    {
        if (!isset($payload['settleAmount'])) {
            return null;
        }
        return Amount::create(floatval($payload['settleAmount']));
    }

    /**
     * @param array $responsePayload Payload
     * @return bool
     */
    private function checkIfIsCrossSale(array $responsePayload): bool
    {
        if (!isset($responsePayload['custom']['mainProductId'])
            || !isset($responsePayload['data']['productId'])
        ) {
            return false;
        }

        return $responsePayload['custom']['mainProductId'] != $responsePayload['data']['productId'];
    }

    /**
     * @param array $payload Payload
     * @return array|null
     */
    private function getSubscriptionArrayFromPayload(array $payload): ?array
    {
        if (!isset($payload['data']['subscriptionId']) || !isset($payload['data']['allMemberSubscriptions'])) {
            return null;
        }

        $subscriptionId         = $payload['data']['subscriptionId'];
        $allMemberSubscriptions = $payload['data']['allMemberSubscriptions'];

        if (!isset($allMemberSubscriptions[$subscriptionId])) {
            return null;
        }

        return $allMemberSubscriptions[$subscriptionId] ?? null;
    }

    /**
     * @param array  $responsePayload Payload
     * @param string $type            Type
     * @param int    $statusCode      status code
     * @return LegacyPostbackBillerResponse
     * @throws Exception
     */
    public static function create(
        array $responsePayload,
        string $type,
        int $statusCode
    ): self {
        if ($statusCode === self::APPROVED_STATUS_CODE) {
            return new static(
                self::CHARGE_RESULT_APPROVED,
                (string) $statusCode,
                self::STATUS_APPROVED,
                null,
                new DateTimeImmutable(),
                $responsePayload,
                new DateTimeImmutable(),
                $type
            );
        }
        //We don't have an specific code to determine
        // when legacy purchase is declined or aborted
        return new static(
            self::CHARGE_RESULT_DECLINED,
            (string) $statusCode,
            self::STATUS_DECLINED,
            null,
            new DateTimeImmutable(),
            $responsePayload,
            new DateTimeImmutable(),
            $type
        );
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isCrossSale(): bool
    {
        return $this->isCrossSale;
    }

    /**
     * @return Amount|null
     */
    public function amount(): ?Amount
    {
        return $this->amount;
    }

    /**
     * @return Rebill|null
     */
    public function rebill(): ?Rebill
    {
        return $this->rebill;
    }

    /**
     * @return string
     */
    public function legacyTransactionId(): string
    {
        return $this->legacyTransactionId;
    }

    /**
     * @return string
     */
    public function legacyMemberId(): string
    {
        return $this->legacyMemberId;
    }

    /**
     * @return string
     */
    public function legacySubscriptionId(): string
    {
        return $this->legacySubscriptionId;
    }
}
