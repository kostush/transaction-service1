<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class EpochBillerChargeSettings extends EpochBillerSettings
{
    /**
     * @var InvoiceId
     */
    protected $invoiceId;

    /**
     * @param string    $clientId              Client If
     * @param string    $clientKey             Client Key
     * @param string    $clientVerificationKey Client Digest Key
     * @param string    $redirectUrl           Redirect Url
     * @param string    $notificationUrl       Notification Url
     * @param InvoiceId $invoiceId             Invoice Id
     */
    private function __construct(
        string $clientId,
        string $clientKey,
        string $clientVerificationKey,
        string $redirectUrl,
        string $notificationUrl,
        InvoiceId $invoiceId
    ) {
        $this->clientId              = $clientId;
        $this->clientKey             = $clientKey;
        $this->clientVerificationKey = $clientVerificationKey;
        $this->redirectUrl           = $redirectUrl;
        $this->notificationUrl       = $notificationUrl;
        $this->invoiceId             = $invoiceId;
    }

    /**
     * @param string    $clientId              Client If
     * @param string    $clientKey             Client Key
     * @param string    $clientVerificationKey Client Digest Key
     * @param string    $redirectUrl           Redirect Url
     * @param string    $notificationUrl       Notification Url
     * @param InvoiceId $invoiceId             Invoice Id
     * @return EpochBillerChargeSettings
     */
    public static function create(
        string $clientId,
        string $clientKey,
        string $clientVerificationKey,
        string $redirectUrl,
        string $notificationUrl,
        InvoiceId $invoiceId
    ): self {
        return new static(
            $clientId,
            $clientKey,
            $clientVerificationKey,
            $redirectUrl,
            $notificationUrl,
            $invoiceId
        );
    }

    /**
     * @return InvoiceId
     */
    public function invoiceId(): InvoiceId
    {
        return $this->invoiceId;
    }

    /**
     * @param array $data retrieved data
     * @return mixed
     * @throws \Exception
     */
    public static function createFromArray(array $data): self
    {
        return static::create(
            $data['clientId'],
            $data['clientKey'],
            $data['clientVerificationKey'],
            $data['redirectUrl'],
            $data['notificationUrl'],
            InvoiceId::createFromString($data['invoiceId'])
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'invoiceId' => (string) $this->invoiceId(),
            ]
        );
    }
}
