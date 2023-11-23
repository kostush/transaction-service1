<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class ChargesCollectionTest
 * @package Tests\Unit\Domain\Model
 */
class ChargesCollectionTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_create_charge_collection_from_array(): void
    {
        $chargesArray[] = $this->createChargeArrayItem();
        $charges        = ChargesCollection::createFromArray($chargesArray);
        $this->assertInstanceOf(ChargesCollection::class, $charges);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_not_create_charges_when_there_is_no_main_purchase(): void
    {
        $this->expectException(MainPurchaseNotFoundException::class);
        $chargesArray[] = $this->createChargeArrayItem(false);
        ChargesCollection::createFromArray($chargesArray);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_not_create_charges_when_there_is_more_than_one_main_purchase(): void
    {
        $this->expectException(NotAllowedMoreThanOneMainPurchaseException::class);
        $chargesArray[] = $this->createChargeArrayItem(true);
        $chargesArray[] = $this->createChargeArrayItem(true);
        ChargesCollection::createFromArray($chargesArray);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_create_charges_when_there_is_one_main_purchase_with_more_than_one_charge(): void
    {
        $chargesArray[] = $this->createChargeArrayItem(true);
        $chargesArray[] = $this->createChargeArrayItem(false);
        $charges        = ChargesCollection::createFromArray($chargesArray);
        $this->assertInstanceOf(ChargesCollection::class, $charges);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function charge_created_from_array_should_be_empty_when_there_is_no_amount(): void
    {
        $chargesArray[]            = $this->createChargeArrayItem();
        $chargesArray[0]['amount'] = null;
        $chargesArray[0]['tax']    = null;
        $charges                   = ChargesCollection::createFromArray($chargesArray);
        $this->assertEmpty($charges->getMainPurchase()->amount()->value());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function charge_created_with_string_amount_should_return_its_right_value(): void
    {
        $stringAmount              = '10.2';
        $chargesArray[]            = $this->createChargeArrayItem();
        $chargesArray[0]['amount'] = $stringAmount;
        $chargesArray[0]['tax']    = null;
        $charges                   = ChargesCollection::createFromArray($chargesArray);
        $this->assertEquals($stringAmount, $charges->getMainPurchase()->amount()->value());
    }

    /**
     * @param bool $mainPurchase Is Main Purchase
     * @return array
     */
    private function createChargeArrayItem(bool $mainPurchase = true): array
    {
        return [
            'siteId'         => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
            "amount"         => 14.97,
            "currency"       => "USD",
            "productId"      => 15,
            "isMainPurchase" => $mainPurchase,
            'rebill'         => [
                'amount'    => 10,
                'frequency' => 365,
                'start'     => 30
            ],
            'tax'            => [
                'initialAmount'    => [
                    'beforeTaxes' => 14.23,
                    'taxes'       => 0.74,
                    'afterTaxes'  => 14.97
                ],
                'rebillAmount'     => [
                    'beforeTaxes' => 9.5,
                    'taxes'       => 0.5,
                    'afterTaxes'  => 10
                ],
                'taxApplicationId' => '60bf5bcb-ac64-496c-acc5-9c7cf54a1869',
                'taxName'          => 'Tax Name',
                'taxRate'          => 0.05,
                'taxType'          => 'vat'
            ]
        ];
    }
}
