<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class CustomerBillingAddress extends BillingAddress
{
    /**
     * @param BillingAddress $billingAddress BillingAddress
     *
     * @return bool
     */
    public function equals(BillingAddress $billingAddress): bool
    {
        return (
            ($this->ownerAddress === $billingAddress->ownerAddress())
            && ($this->ownerCity === $billingAddress->ownerCity())
            && ($this->ownerCountry === $billingAddress->ownerCountry())
            && ($this->ownerState === $billingAddress->ownerState())
            && ($this->ownerZip === $billingAddress->ownerZip())
            && ($this->ownerPhoneNo === $billingAddress->ownerPhoneNo())
        );
    }
}