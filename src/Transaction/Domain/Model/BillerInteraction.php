<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use DateTime;
use DateTimeImmutable;
use DomainException;
use Exception;
use Google\Cloud\Core\Timestamp;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;

class BillerInteraction implements ObfuscatedData
{
    const TYPE_REQUEST  = 'request';
    const TYPE_RESPONSE = 'response';

    const TYPES = [
        self::TYPE_REQUEST,
        self::TYPE_RESPONSE
    ];

    /**
     * @var BillerInteractionId
     */
    private $billerInteractionId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string json
     */
    private $payload;

    /**
     * @var DateTimeImmutable
     */
    private $createdAt;

    /**
     * BillerInteraction constructor.
     * @param string              $type                Biller type
     * @param string              $payload             Payload
     * @param DateTimeImmutable   $createdAt           Creation Date
     * @param BillerInteractionId $billerInteractionId id
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    private function __construct(
        string $type,
        string $payload,
        DateTimeImmutable $createdAt,
        ?BillerInteractionId $billerInteractionId
    ) {
        $this->type                = $this->initType($type);
        $this->payload             = $this->initPayload($payload);
        $this->createdAt           = $createdAt;
        $this->billerInteractionId = $billerInteractionId;
    }

    /**
     * @param string              $type                Biller type
     * @param string              $payload             Payload
     * @param DateTimeImmutable   $createdAt           Creation Date
     * @param BillerInteractionId $billerInteractionId id
     * @return BillerInteraction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    public static function create(
        string $type,
        string $payload,
        DateTimeImmutable $createdAt,
        $billerInteractionId = null
    ): self {
        return new static(
            $type,
            self::obfuscateData($payload),
            $createdAt,
            $billerInteractionId
        );
    }

    /**
     * @param string $payload The Interaction payload
     * @return string
     * @throws DomainException
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     */
    private static function obfuscateData($payload): string
    {
        $payload = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw  new InvalidBillerInteractionPayloadException();
        }

        $obfuscationKeys = [
            'cardNo',
            'cvv',
            'cvv2',
            'cardNumber',
            'cardCvv2',
            'routingNo',
            'accountNo',
            'ssNumber'
        ];

        foreach ($obfuscationKeys as $obfuscateKey) {
            if (!empty($payload[$obfuscateKey])) {
                $payload[$obfuscateKey] = self::OBFUSCATED_STRING;
            }
        };

        return json_encode($payload);
    }

    /**
     * Get $type
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get $payload
     * @return string json
     */
    public function payload(): string
    {
        return $this->payload;
    }

    /**
     * Get $payload
     * @return DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return BillerInteractionId
     */
    public function billerInteractionId(): BillerInteractionId
    {
        return $this->billerInteractionId;
    }

    /**
     * @param BillerInteraction $billerInteraction Biller interaction to check
     * @return bool
     */
    public function equals(
        BillerInteraction $billerInteraction
    ): bool {
        return (
            ($this->type() === $billerInteraction->type())
            && ($this->payload() === $billerInteraction->payload())
            && ($this->createdAt()->getTimestamp() === $billerInteraction->createdAt()->getTimestamp())
        );
    }

    /**
     * @param string $payload Payload JSON
     * @return string
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     */
    private function initPayload(
        string $payload
    ): string {
        json_decode($payload);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw  new InvalidBillerInteractionPayloadException();
        }

        return $payload;
    }

    /**
     * @param string $type Interaction Type
     * @return string
     * @throws Exception
     * @throws InvalidBillerInteractionTypeException
     */
    private function initType(
        string $type
    ): string {
        if (!in_array($type, self::TYPES)) {
            throw  new InvalidBillerInteractionTypeException($type);
        }

        return $type;
    }

    /**
     * @return bool
     */
    public function isRequestType(): bool
    {
        return ($this->type === self::TYPE_REQUEST);
    }

    /**
     * @return bool
     */
    public function isResponseType(): bool
    {
        return ($this->type === self::TYPE_RESPONSE);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => $this->type(),
            'payload'   => !empty($this->payload) ? json_decode($this->payload, true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR) : null,
            'createdAt' => new Timestamp(DateTime::createFromImmutable($this->createdAt()))
        ];
    }
}
