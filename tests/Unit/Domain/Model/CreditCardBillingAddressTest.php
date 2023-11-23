<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use Tests\UnitTestCase;

class CreditCardBillingAddressTest extends UnitTestCase
{
    /**
     * @test
     * @return CreditCardBillingAddress
     * @throws \Exception
     */
    public function create_should_return_object_when_given_the_correct_data()
    {
        $creditCardBillingAddress = $this->createCreditCardBillingAddress(
            [
                'ownerAddress'     => 'address',
                'ownerCity'        => 'city',
                'ownerCountry'     => 'country',
                'ownerState'       => 'state',
                'ownerZip'         => '789955',
                'ownerPhoneNumber' => '789798798'
            ]
        );

        $this->assertInstanceOf(CreditCardBillingAddress::class, $creditCardBillingAddress);
        return $creditCardBillingAddress;
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_address(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('address', $creditCardBillingAddress->ownerAddress());
    }


    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_city(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('city', $creditCardBillingAddress->ownerCity());
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_country(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('country', $creditCardBillingAddress->ownerCountry());
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_state(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('state', $creditCardBillingAddress->ownerState());
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_zip(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('789955', $creditCardBillingAddress->ownerZip());
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function created_should_have_owner_phone_no(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $this->assertEquals('789798798', $creditCardBillingAddress->ownerPhoneNo());
    }


    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function equals_should_return_true_when_comparing_two_objects_with_the_same_attributes(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $newCreditCardBillingAddress = $this->createCreditCardBillingAddress(
            [
                'ownerAddress'     => 'address',
                'ownerCity'        => 'city',
                'ownerCountry'     => 'country',
                'ownerState'       => 'state',
                'ownerZip'         => '789955',
                'ownerPhoneNumber' => '789798798'
            ]
        );
        $this->assertEquals(true, $creditCardBillingAddress->equals($newCreditCardBillingAddress));
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardBillingAddress $creditCardBillingAddress CreditCardNumber object
     * @return void
     */
    public function equals_should_return_false_when_comparing_two_objects_with_different_attributes(CreditCardBillingAddress $creditCardBillingAddress)
    {
        $newCreditCardBillingAddress = $this->createCreditCardBillingAddress(['ownerZip' => '99999']);
        $this->assertEquals(false, $creditCardBillingAddress->equals($newCreditCardBillingAddress));
    }
}
