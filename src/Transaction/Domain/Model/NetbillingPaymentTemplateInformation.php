<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use JMS\Serializer\SerializerBuilder;

class NetbillingPaymentTemplateInformation extends PaymentInformation
{
    /**
     * @var NetbillingCardHash
     */
    protected $netbillingCardHash;

    /**
     * PaymentTemplateInformation constructor.
     * @param NetbillingCardHash $netbillingCardHash The netbilling card hash
     */
    private function __construct(NetbillingCardHash $netbillingCardHash)
    {
        $this->netbillingCardHash = $netbillingCardHash;
    }

    /**
     * @param NetbillingCardHash $netbillingCardHash The netbilling card hash
     * @return NetbillingPaymentTemplateInformation
     */
    public static function create(NetbillingCardHash $netbillingCardHash): self
    {
        return new static($netbillingCardHash);
    }

    /**
     * @return NetbillingCardHash
     */
    public function netbillingCardHash(): NetbillingCardHash
    {
        return $this->netbillingCardHash;
    }

    /**
     * Get cvv2Check
     * @return void
     */
    public function cvv2Check(): void
    {
        //return empty value for domain events
    }

    /**
     * Get cvv2Check
     * @return void
     */
    public function creditCardOwner(): void
    {
        //return empty value for domain events
    }

    /**
     * Get cvv2Check
     * @return void
     */
    public function creditCardBillingAddress(): void
    {
        //return empty value for domain events
    }

    /**
     * Get cvv2Check
     * @return void
     */
    public function expirationYear(): void
    {
        //return empty value for domain events
    }

    /**
     * Get cvv2Check
     * @return void
     */
    public function expirationMonth(): void
    {
        //return empty value for domain events
    }

    /**
     * @return NetbillingPaymentTemplateInformation
     */
    public function returnDataForPersistence(): NetbillingPaymentTemplateInformation
    {
        return new static(
            $this->NetbillingCardHash()
        );
    }

    /**
     * @return array
     */
    public function detailedInformation(): array
    {
        return [
            'card_hash' => $this->netbillingCardHash()
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'netbillingCardHash' => [
                'value' => $this->netbillingCardHash()->value()
            ]
        ];
    }
}
