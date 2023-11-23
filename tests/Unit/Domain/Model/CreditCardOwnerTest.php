<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Email;
use Tests\UnitTestCase;

class CreditCardOwnerTest extends UnitTestCase
{
    /**
     * @test
     * @return CreditCardOwner
     * @throws \Exception
     */
    public function create_should_return_a_credit_card_owner_when_correct_data_is_provided()
    {
        $creditCardOwner = $this->createCreditCardOwner(
            [
                'ownerFirstName'   => 'FirstName',
                'ownerLastName'    => 'LastName',
                'email'            => 'email@email.com',
                'ownerUserName'    => 'user1234',
                'ownerPassword'    => 'password123'
            ]
        );

        $this->assertInstanceOf(CreditCardOwner::class, $creditCardOwner);

        return $creditCardOwner;
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Previously created CreditCard Owner object
     * @return void
     */
    public function created_credit_card_owner_should_have_a_first_name(CreditCardOwner $creditCardOwner)
    {
        $this->assertEquals('FirstName', $creditCardOwner->ownerFirstName());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Previously created CreditCard Owner object
     * @return void
     */
    public function created_credit_card_owner_should_have_a_last_name(CreditCardOwner $creditCardOwner)
    {
        $this->assertEquals('LastName', $creditCardOwner->ownerLastName());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Previously created CreditCard Owner object
     * @return void
     */
    public function created_credit_card_owner_should_have_a_password(CreditCardOwner $creditCardOwner)
    {
        $this->assertEquals('password123', $creditCardOwner->ownerPassword());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Previously created CreditCard Owner object
     * @return void
     */
    public function created_credit_card_owner_should_have_a_user_name(CreditCardOwner $creditCardOwner)
    {
        $this->assertEquals('user1234', $creditCardOwner->ownerUserName());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Previously created CreditCard Owner object
     * @return void
     */
    public function created_criteria_should_have_a_email_address(CreditCardOwner $creditCardOwner)
    {
        $this->assertInstanceOf(Email::class, $creditCardOwner->ownerEmail());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Credit card owner object
     * @return void
     * @throws \Exception
     */
    public function credit_card_owner_should_return_true_when_equal(CreditCardOwner $creditCardOwner)
    {
        $equalCreditCardOwner = $this->createCreditCardOwner(
            [
                'ownerFirstName'   => $creditCardOwner->ownerFirstName(),
                'ownerLastName'    => $creditCardOwner->ownerLastName(),
                'email'            => $creditCardOwner->ownerEmail()->email(),
                'ownerUserName'    => $creditCardOwner->ownerUserName(),
                'ownerPassword'    => $creditCardOwner->ownerPassword(),
            ]
        );

        $this->assertTrue($creditCardOwner->equals($equalCreditCardOwner));
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_owner_when_correct_data_is_provided
     * @param CreditCardOwner $creditCardOwner Credit card owner object
     * @return void
     * @throws \Exception
     */
    public function credit_card_owner_should_return_false_when_equal(CreditCardOwner $creditCardOwner)
    {
        $equalCreditCardOwner = $this->createCreditCardOwner(['ownerLastName' => 'DifferentName']);

        $this->assertFalse($creditCardOwner->equals($equalCreditCardOwner));
    }
}
