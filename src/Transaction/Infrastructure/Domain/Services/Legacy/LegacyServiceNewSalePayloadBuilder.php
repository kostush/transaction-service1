<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use ProbillerNG\LegacyServiceClient\Model\BillerFields;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlRequest;
use ProbillerNG\LegacyServiceClient\Model\Item;
use ProbillerNG\LegacyServiceClient\Model\ItemRebill;
use ProbillerNG\LegacyServiceClient\Model\NewMember;
use ProbillerNG\LegacyServiceClient\Model\Payment as LegacyServicePayment;
use ProbillerNG\LegacyServiceClient\Model\PaymentInformation;
use ProbillerNG\LegacyServiceClient\Model\TaxAmount;
use ProbillerNG\LegacyServiceClient\Model\TaxInfo;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Charge;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\TaxAmount as TaxInformationAmount;
use ProBillerNG\Transaction\Domain\Model\TaxInformation;

class LegacyServiceNewSalePayloadBuilder
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @param ChargesCollection $charges     Charges
     * @param Member            $member      Member
     * @return GeneratePurchaseUrlRequest
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    public function createPurchaseUrlPayload(
        ChargeTransaction $transaction,
        ChargesCollection $charges,
        ?Member $member
    ): GeneratePurchaseUrlRequest {
        return (new GeneratePurchaseUrlRequest())
            ->setCharges($this->returnCharges($charges))
            ->setPayment(
                $this->returnPayment(
                    $transaction,
                    $member
                )
            )
            ->setBillerFields(
                $this->returnBillerFields(
                    $transaction->billerChargeSettings(),
                    $transaction->isFreeSale()
                )
            );
    }

    /**
     * @param BillerSettings $billerSettings Biller Settings
     * @param bool           $isFreeSale     Is free sale.
     * @return BillerFields
     */
    private function returnBillerFields(BillerSettings $billerSettings, bool $isFreeSale): BillerFields
    {
        /** @var LegacyBillerChargeSettings $billerSettings */
        return (new BillerFields())->setBillerName($billerSettings->billerName())
            ->setLegacyMemberId($billerSettings->legacyMemberId())
            ->setReturnUrl($billerSettings->returnUrl())
            ->setIsCcAuth($isFreeSale)
            ->setOthers($billerSettings->others());
    }

    /**
     * @param Member|null $member Member
     * @return NewMember|null
     */
    private function returnMember(?Member $member): ?NewMember
    {
        if (empty($member)) {
            return null;
        }

        return (new NewMember())
            ->setPassword($member->password())
            ->setUserName($member->userName())
            ->setAddress($member->address())
            ->setCity($member->city())
            ->setCountry($member->country())
            ->setEmail($member->email())
            ->setFirstName($member->firstName())
            ->setLastName($member->lastName())
            ->setPhone($member->phone())
            ->setState($member->state())
            ->setZipCode($member->zipCode());
    }

    /**
     * @param ChargesCollection $chargesCollection Charges
     * @return array
     */
    private function returnCharges(ChargesCollection $chargesCollection): array
    {
        $arrayOrCharges = [];
        foreach ($chargesCollection as $charge) {
            /**  @var Charge $charge * */
            $arrayOrCharges[] = (new Item())
                ->setProductId($charge->productId())
                ->setAmount(!empty($charge->amount()) ? $charge->amount()->value() : null)
                ->setCurrency((string) $charge->currency())
                ->setIsMainPurchase($charge->isMainPurchase())
                ->setRebill($this->returnRebill($charge->rebill()))
                ->setTax($this->returnTax($charge->taxInformation()));
        }
        return $arrayOrCharges;
    }

    /**
     * @param Rebill|null $rebill Rebill
     * @return ItemRebill
     */
    private function returnRebill(?Rebill $rebill): ?ItemRebill
    {
        if (empty($rebill)) {
            return null;
        }

        return (new ItemRebill())->setAmount($rebill->amount()->value())
            ->setFrequency($rebill->frequency())
            ->setStart($rebill->start());
    }

    /**
     * @param TaxInformation|null $tax Tax
     * @return TaxInfo|null
     */
    private function returnTax(?TaxInformation $tax): ?TaxInfo
    {
        if (empty($tax)) {
            return null;
        }

        return (new TaxInfo())
            ->setTaxRate($tax->taxRate())
            ->setTaxType($tax->taxType())
            ->setTaxName($tax->taxName())
            ->setTaxApplicationId($tax->taxApplicationId())
            ->setDisplayChargedAmount($tax->displayChargedAmount())
            ->setRebillAmount($this->returnTaxAmount($tax->rebillAmount()))
            ->setInitialAmount($this->returnTaxAmount($tax->initialAmount()));
    }

    /**
     * @param TaxInformationAmount $taxAmount Tax
     * @return TaxAmount
     */
    private function returnTaxAmount(?TaxInformationAmount $taxAmount): ?TaxAmount
    {
        if (empty($taxAmount)) {
            return null;
        }

        return (new TaxAmount())->setTaxes($taxAmount->taxes()->value())
            ->setAfterTaxes($taxAmount->afterTaxes()->value())
            ->setBeforeTaxes($taxAmount->beforeTaxes()->value());
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param Member|null       $member      Member
     * @return LegacyServicePayment|null
     */
    private function returnPayment(ChargeTransaction $transaction, ?Member $member): ?LegacyServicePayment
    {
        return (new LegacyServicePayment)
            ->setMethod($transaction->paymentMethod())
            ->setType($transaction->paymentType())
            ->setInformation(
                (new PaymentInformation())->setMember($this->returnMember($member))
            );
    }
}
