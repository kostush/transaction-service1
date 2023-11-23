<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use Tests\UnitTestCase;

class CreditCardInformationTest extends UnitTestCase
{
    /**
     * @test
     * @return CreditCardInformation
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     */
    public function create_should_return_a_credit_card_information_object_when_correct_data_is_provided()
    {
        $creditCardInformation = $this->createCreditCardInformation(
            [
                'cvv'              => '123',
                'expirationMonth'  => 9,
                'expirationYear'   => 2030,
                'ownerAddress'     => 'address',
                'ownerCity'        => 'city',
                'ownerCountry'     => 'country',
                'ownerState'       => 'state',
                'ownerZip'         => '789955',
                'ownerPhoneNumber' => '789798798',
                'ownerFirstName'   => 'FirstName',
                'ownerLastName'    => 'LastName',
                'email'            => 'email@email.com'
            ]
        );

        $this->assertInstanceOf(CreditCardInformation::class, $creditCardInformation);
        return $creditCardInformation;
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_cvv_2_check(CreditCardInformation $creditCardInformation)
    {
        $this->assertEquals(true, $creditCardInformation->cvv2Check());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_credit_card_number(CreditCardInformation $creditCardInformation)
    {
        $this->assertInstanceOf(CreditCardNumber::class, $creditCardInformation->creditCardNumber());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_credit_card_owner(CreditCardInformation $creditCardInformation)
    {
        $this->assertInstanceOf(CreditCardOwner::class, $creditCardInformation->creditcardOwner());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_credit_card_billing_address(CreditCardInformation $creditCardInformation)
    {
        $this->assertInstanceOf(CreditCardBillingAddress::class, $creditCardInformation->creditCardBillingAddress());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_cvv(CreditCardInformation $creditCardInformation)
    {
        $this->assertEquals("123", $creditCardInformation->cvv());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_expiration_month(CreditCardInformation $creditCardInformation)
    {
        $this->assertEquals(9, $creditCardInformation->expirationMonth());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     */
    public function created_should_have_expiration_year(CreditCardInformation $creditCardInformation)
    {
        $this->assertEquals(2030, $creditCardInformation->expirationYear());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     */
    public function created_equal_should_return_true_for_object_with_the_same_attributes(
        CreditCardInformation $creditCardInformation
    ) {
        $newCreditCardInformation = $this->createCreditCardInformation(
            [
                'cvv'              => $creditCardInformation->cvv(),
                'expirationMonth'  => $creditCardInformation->expirationMonth(),
                'expirationYear'   => $creditCardInformation->expirationYear(),
                'ownerAddress'     => $creditCardInformation->creditCardBillingAddress()->ownerAddress(),
                'ownerCity'        => $creditCardInformation->creditCardBillingAddress()->ownerCity(),
                'ownerCountry'     => $creditCardInformation->creditCardBillingAddress()->ownerCountry(),
                'ownerState'       => $creditCardInformation->creditCardBillingAddress()->ownerState(),
                'ownerZip'         => $creditCardInformation->creditCardBillingAddress()->ownerZip(),
                'ownerPhoneNumber' => $creditCardInformation->creditCardBillingAddress()->ownerPhoneNo(),
                'ownerFirstName'   => $creditCardInformation->creditCardOwner()->ownerFirstName(),
                'ownerLastName'    => $creditCardInformation->creditCardOwner()->ownerLastName(),
                'ownerUserName'    => $creditCardInformation->creditCardOwner()->ownerUserName(),
                'ownerPassword'    => $creditCardInformation->creditCardOwner()->ownerPassword(),
                'email'            => $creditCardInformation->creditCardOwner()->ownerEmail()->email()
            ]
        );
        $this->assertEquals(true, $creditCardInformation->equals($newCreditCardInformation));
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @return void
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     */
    public function created_equal_should_return_false_for_object_with_different_attributes(
        CreditCardInformation $creditCardInformation
    ) {
        $newCreditCardInformation = $this->createCreditCardInformation(['cvv' => '999']);

        $this->assertEquals(false, $creditCardInformation->equals($newCreditCardInformation));
    }

    /**
     * @test
     * @return void
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     */
    public function create_should_throw_an_invalid_argument_exception_when_invalid_expiration_date_is_sent()
    {
        $this->expectException(InvalidCreditCardExpirationDateException::class);
        $this->createCreditCardInformation(['expirationYear' => 2000]);
    }

    /**
     * @test
     * @return void
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     */
    public function create_should_throw_an_invalid_argument_exception_when_invalid_cvv_is_sent()
    {
        $this->expectException(InvalidCreditCardCvvException::class);
        $this->createCreditCardInformation(['cvv' => 'invalid']);
    }

    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_with_invalid_number_should_throw_invalid_credit_card_information_exception()
    {
        $this->expectException(InvalidCreditCardNumberException::class);
        $this->createCreditCardInformation(['number' => '11']);
    }

    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_with_invalid_expiration_month_should_throw_invalid_credit_card_information_exception()
    {
        $this->expectException(InvalidCreditCardExpirationDateException::class);
        $this->createCreditCardInformation(['expirationMonth' => '15']);
    }

    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_with_invalid_expiration_year_should_throw_invalid_credit_card_information_exception()
    {
        $this->expectException(InvalidCreditCardExpirationDateException::class);
        $this->createCreditCardInformation(['expirationYear' => '1066']);
    }

    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_with_invalid_cvv_should_throw_invalid_credit_card_information_exception()
    {
        $this->expectException(InvalidCreditCardNumberException::class);
        $this->createCreditCardInformation(['number' => '4444']);
    }


    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_without_year_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new NewCreditCardInformation(
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            (string) $this->faker->numberBetween(1, 12),
            null,
            (string) $this->faker->numberBetween(100, 999),
            $this->createCommandMember()
        );
    }

    /**
     * @test
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return void
     */
    public function create_without_month_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new  NewCreditCardInformation(
            $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            null,
            $this->faker->numberBetween(2025, 2030),
            (string) $this->faker->numberBetween(100, 999),
            $this->createCommandMember()
        );
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @throws \Exception
     * @return void
     */
    public function return_obfuscated_data_for_persistence_should_return_obfuscated_card_number(CreditCardInformation $creditCardInformation)
    {
        $od = $creditCardInformation->returnObfuscatedDataForPersistence();
        $this->assertSame(self::OBFUSCATED_STRING, $od->creditCardNumber()->cardNumber());
    }

    /**
     * @test
     * @depends create_should_return_a_credit_card_information_object_when_correct_data_is_provided
     * @param CreditCardInformation $creditCardInformation CreditCardOwner object
     * @throws \Exception
     * @return void
     */
    public function return_obfuscated_data_for_persistence_should_return_obfuscated_cvv(CreditCardInformation $creditCardInformation)
    {
        //returnObfuscatedDataForPersistence
        $od = $creditCardInformation->returnObfuscatedDataForPersistence();
        $this->assertSame(self::OBFUSCATED_STRING, $od->cvv());
    }


}
