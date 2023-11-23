<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate;

class RocketgateBillerTransaction
{
    const SALE_TYPE = 'sale';

    const AUTH_TYPE = 'auth';

    const THREE_D_SECURED_TYPE = '3ds';

    const CARD_UPLOAD_TYPE = 'cardUpload';

    const FREE_SALE_APPROVED_AMOUNT = '1.02';

    /**
     * @var null|string
     */
    private $invoiceId;

    /**
     * @var null|string
     */
    private $customerId;

    /**
     * @var null|string
     */
    private $billerTransactionId;

    /**
     * @var null|string
     */
    private $type;

    /**
     * BillerTransaction constructor.
     * @param null|string $invoiceId           The invoice id
     * @param null|string $customerId          The customer id
     * @param null|string $billerTransactionId The biller transaction id
     * @param null|string $type                The transaction type
     */
    public function __construct(
        ?string $invoiceId,
        ?string $customerId,
        ?string $billerTransactionId,
        ?string $type
    ) {
        $this->invoiceId           = $invoiceId;
        $this->customerId          = $customerId;
        $this->billerTransactionId = $billerTransactionId;
        $this->type                = $type;
    }

    /**
     * @return null|string
     */
    public function invoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * @return null|string
     */
    public function customerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @return null|string
     */
    public function billerTransactionId(): ?string
    {
        return $this->billerTransactionId;
    }

    /**
     * @return null|string
     */
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "invoiceId"            => $this->invoiceId(),
            "customerId"           => $this->customerId(),
            "billerTransactionId"  => $this->billerTransactionId(),
            "type"                 => $this->type()
        ];
    }
}
