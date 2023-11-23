<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use JMS\Serializer\SerializerBuilder;

class PaymentTemplateInformation extends PaymentInformation
{
    /**
     * @var RocketGateCardHash
     */
    protected $rocketGateCardHash;

    /**
     * @var RocketGateCardHash
     */
    protected $rocketgateCardHash;


    /**
     * PaymentTemplateInformation constructor.
     * @param RocketGateCardHash $rocketGateCardHash The rocket gate card hash
     */
    private function __construct(RocketGateCardHash $rocketGateCardHash)
    {
        $this->rocketGateCardHash = $rocketGateCardHash;
        // TODO done to align with the new TS
        $this->rocketgateCardHash = $rocketGateCardHash;
    }

    /**
     * @param RocketGateCardHash $rocketGateCardHash The rocket gate card hash
     * @return PaymentTemplateInformation
     */
    public static function create(RocketGateCardHash $rocketGateCardHash): self
    {
        return new static($rocketGateCardHash);
    }

    /**
     * @return RocketGateCardHash
     */
    public function rocketGateCardHash(): RocketGateCardHash
    {
        // TODO done to align with the new TS
        return $this->rocketGateCardHash ?? $this->rocketgateCardHash;
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
     * @return array
     */
    public function returnDataForPersistence(): PaymentTemplateInformation
    {
        return new static(
            $this->rocketGateCardHash()
        );
    }

    /**
     * @return array
     */
    public function detailedInformation(): array
    {
        return [
            'card_hash' => $this->rocketGateCardHash()->value()
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'rocketgateCardHash' => [
                'value' => $this->rocketgateCardHash()->value()
            ]
        ];
    }
}
