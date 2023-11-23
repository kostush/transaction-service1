<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling;

class NetbillingBillerTransaction
{
    const SALE_TYPE = 'sale';

    const AUTH_TYPE = 'auth';

    const FREE_SALE_APPROVED_AMOUNT = '0';

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
     * @param null|string $customerId          The customer id
     * @param null|string $billerTransactionId The biller transaction id
     * @param null|string $type                The transaction type
     */
    public function __construct(
        ?string $customerId,
        ?string $billerTransactionId,
        ?string $type
    ) {
        $this->customerId          = $customerId;
        $this->billerTransactionId = $billerTransactionId;
        $this->type                = $type;
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
            "customerId"           => $this->customerId(),
            "billerTransactionId" => $this->billerTransactionId(),
            "type"                  => $this->type()
        ];
    }
}
