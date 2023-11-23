<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class CreditCardBillingAddress extends BillingAddress
{
    /**
     * @param BillingAddress $creditCardBillingAddress CreditCardBillingAddress object
     *
     * @return bool
     */
    public function equals(BillingAddress $creditCardBillingAddress): bool
    {
        return (
            ($this->ownerAddress === $creditCardBillingAddress->ownerAddress())
            && ($this->ownerCity === $creditCardBillingAddress->ownerCity())
            && ($this->ownerCountry === $creditCardBillingAddress->ownerCountry())
            && ($this->ownerState === $creditCardBillingAddress->ownerState())
            && ($this->ownerZip === $creditCardBillingAddress->ownerZip())
            && ($this->ownerPhoneNo === $creditCardBillingAddress->ownerPhoneNo())
        );
    }
}
