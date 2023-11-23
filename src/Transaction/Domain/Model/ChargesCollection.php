<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Collection;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;

class ChargesCollection extends Collection
{
    /**
     * @var bool
     */
    private $thereIsMainPurchaseAlready = false;

    /**
     * Validates the object
     * @param mixed $object object
     * @return bool
     */
    protected function isValidObject($object)
    {
        return $object instanceof Charge;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->getValues() as $object) {
            /** @var Charge $object */
            $data[] = $object->toArray();
        }

        return $data;
    }

    /**
     * Creating when restore the session
     * @param array $arrayChargesCollection Charges Collection
     * @return ChargesCollection
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public static function createFromArray(array $arrayChargesCollection): self
    {
        $collection = new ChargesCollection();

        foreach ($arrayChargesCollection as $arrayCharge) {
            $collection->add(
                Charge::create(
                    $arrayCharge['currency'],
                    $arrayCharge['productId'],
                    $arrayCharge['siteId'],
                    $arrayCharge['isMainPurchase'],
                    (isset($arrayCharge['amount']) ? floatval($arrayCharge['amount']) : null),
                    $arrayCharge['preChecked'] ?? null,
                    $arrayCharge['rebill'] ?? null,
                    $arrayCharge['tax'] ?? null
                )
            );
        }

        if (!$collection->thereIsMainPurchaseAlready()) {
            throw new MainPurchaseNotFoundException();
        }

        return $collection;
    }

    /**
     * @return Charge
     * @throws MainPurchaseNotFoundException
     * @throws Exception
     */
    public function getMainPurchase(): Charge
    {
        foreach ($this->getValues() as $charge) {
            /** @var Charge $charge */
            if ($charge->isMainPurchase()) {
                return $charge;
            }
        }
        throw new MainPurchaseNotFoundException();
    }

    /**
     * @param Charge $element Charge.
     * @return bool|true
     * @throws Exception
     * @throws NotAllowedMoreThanOneMainPurchaseException
     */
    public function add($element): bool
    {
        if ($this->thereIsMainPurchaseAlready() && $element->isMainPurchase()) {
            throw new NotAllowedMoreThanOneMainPurchaseException();
        }

        if ($element->isMainPurchase()) {
            $this->thereIsMainPurchaseAlready = $element->isMainPurchase();
        }
        return parent::add($element);
    }

    /**
     * @return bool
     */
    public function thereIsMainPurchaseAlready(): bool
    {
        return $this->thereIsMainPurchaseAlready;
    }
}
