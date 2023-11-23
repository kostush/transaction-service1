<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class QyssoBillerSettings implements BillerSettings, ObfuscatedData
{
    /**
     * @var string
     */
    protected $companyNum;

    /**
     * @var string
     */
    protected $personalHashKey;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    protected $notificationUrl;

    /**
     * QyssoBillerSettings constructor.
     * @param string $companyNum
     * @param string $personalHashKey
     * @param string $redirectUrl
     * @param string $notificationUrl
     */
    public function __construct(
        string $companyNum,
        string $personalHashKey,
        string $redirectUrl,
        string $notificationUrl
    ) {
        $this->companyNum      = $companyNum;
        $this->personalHashKey = $personalHashKey;
        $this->redirectUrl     = $redirectUrl;
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * @param array $data retrieved data
     * @return mixed
     * @throws \Exception
     */
    public static function createFromArray(array $data): self
    {
        return static::create(
            $data['company_num'],
            $data['personal_hash_key'],
            $data['redirect_url'],
            $data['notification_url'],
        );
    }

    /**
     * @param string $companyNum
     * @param string $personalHashKey
     * @param string $redirectUrl
     * @param string $notificationUrl
     * @return static
     */
    public static function create(
        string $companyNum,
        string $personalHashKey,
        string $redirectUrl,
        string $notificationUrl
    ): self {
        return new static(
            $companyNum,
            $personalHashKey,
            $redirectUrl,
            $notificationUrl,
        );
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return self::QYSSO;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'companyNum'      => $this->companyNum(),
            'personalHashKey' => $this->personalHashKey(),
            'notificationUrl' => $this->notificationUrl(),
            'redirectUrl'     => $this->redirectUrl(),
        ];
    }

    /**
     * @return string
     */
    public function companyNum(): string
    {
        return $this->companyNum;
    }

    /**
     * @return string
     */
    public function personalHashKey(): string
    {
        return $this->personalHashKey;
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
    public function redirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
