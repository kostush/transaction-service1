<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class EpochBillerSettings implements BillerSettings, ObfuscatedData
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientKey;

    /**
     * @var string
     */
    protected $clientVerificationKey;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    protected $notificationUrl;

    /**
     * @param array $data
     * @return mixed
     */
    abstract public static function createFromArray(array $data);

    /**
     * @var string
     */
    protected $billerMemberId = '12345'; //TODO remove mock value

    /**
     * @var string
     */
    protected $piCode = 'piCode'; //TODO remove mock value

    /**
     * @var string
     */
    protected $coCode = 'coCode'; //TODO remove mock value

    /**
     * @return string
     */
    public function clientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function clientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * @return string
     */
    public function clientVerificationKey(): string
    {
        return $this->clientVerificationKey;
    }

    /**
     * @return string
     */
    public function redirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @return string
     */
    public function notificationUrl(): string
    {
        return $this->notificationUrl;
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return self::EPOCH;
    }

    /**
     * @return string
     */
    public function billerMemberId(): string
    {
        return $this->billerMemberId;
    }

    /**
     * @return string
     */
    public function piCode(): string
    {
        return $this->piCode;
    }

    /**
     * @return string|null
     */
    public function coCode(): ?string
    {
        return $this->coCode;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'clientId'              => $this->clientId(),
            'clientKey'             => $this->clientKey(),
            'clientVerificationKey' => $this->clientVerificationKey(),
            'redirectUrl'           => $this->redirectUrl(),
            'notificationUrl'       => $this->notificationUrl(),
        ];
    }
}
