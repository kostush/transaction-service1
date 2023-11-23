<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use Tests\UnitTestCase;

class CreditCardNumberTest extends UnitTestCase
{
    /**
     * @test
     * @return CreditCardNumber
     * @throws \Exception
     */
    public function create_should_return_object_when_given_the_correct_data()
    {
        $cardNumber = $this->createCreditCardNumber();
        $this->assertInstanceOf(CreditCardNumber::class, $cardNumber);
        return $cardNumber;
    }

    /**
     * @test
     * @return CreditCardNumber
     * @throws \Exception
     */
    public function create_should_return_object_when_given_the_correct_mir_data()
    {
        $cardNumber = $this->createCreditCardNumber(['number' => '2201 0093 2891 0009']);
        $this->assertInstanceOf(CreditCardNumber::class, $cardNumber);
        return $cardNumber;
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_mir_data
     * @param CreditCardNumber $cardNumber CreditCardNumber object
     * @return void
     */
    public function created_should_have_mir_card_number(CreditCardNumber $cardNumber)
    {
        $this->assertEquals('2201009328910009', $cardNumber->cardNumber());
    }

    /**
     * @test
     * @return CreditCardNumber
     * @throws \Exception
     */
    public function create_should_return_object_when_given_the_wrong_mir_data()
    {
        $this->expectException(InvalidCreditCardNumberException::class);
        $this->createCreditCardNumber(['number' => '2201 0093 2891 0008']);
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardNumber $cardNumber CreditCardNumber object
     * @return void
     */
    public function created_should_have_card_number(CreditCardNumber $cardNumber)
    {
        $this->assertEquals($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], $cardNumber->cardNumber());
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardNumber $cardNumber CreditCardNumber object
     * @return void
     */
    public function created_equal_should_return_true_for_object_with_the_same_attributes(CreditCardNumber $cardNumber)
    {
        $newCardNumber = $this->createCreditCardNumber();
        $this->assertEquals(true, $cardNumber->equals($newCardNumber));
    }

    /**
     * @test
     * @depends create_should_return_object_when_given_the_correct_data
     * @param CreditCardNumber $cardNumber CreditCardNumber object
     * @return void
     */
    public function created_equal_should_return_false_for_object_with_different_attributes(CreditCardNumber $cardNumber)
    {
        // AUTO GENERATED CARD. NOT A REAL ONE
        $newCardNumber = $this->createCreditCardNumber(['number' => $this->faker->creditCardNumber('Visa')]);
        $this->assertEquals(false, $cardNumber->equals($newCardNumber));
    }


    /**
     * @test
     * @return void
     * @throws InvalidCreditCardInformationException
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function create_should_throw_an_exception_when_invalid_data_is_provided()
    {
        $this->expectException(InvalidCreditCardNumberException::class);
        $this->createCreditCardNumber(['number' => '1231231321321321321']);
    }

    /**
     * @test
     * @return void
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function create_should_pass_successfully_when_amex_cc_provided()
    {
        $amexCreditCard = $this->faker->creditCardNumber('American Express');
        $cardNumber = $this->createCreditCardNumber(['number' => $amexCreditCard]);

        $this->assertInstanceOf(CreditCardNumber::class, $cardNumber);
        $this->assertEquals(true, $cardNumber->equals($cardNumber));
        $this->assertEquals('amex', $cardNumber->cardType);
        $this->assertEquals($amexCreditCard, $cardNumber->cardNumber());
    }

    /**
     * @test
     * @return void
     */
    public function create_obfuscated_should_return_obfuscated_object()
    {
        $data = [
            'type' => '',
            'number' => $_ENV['ROCKETGATE_COMMON_CARD_NUMBER'],
            'valid' => true,
            'firstSix' => $_ENV['ROCKETGATE_CARD_FIRST_SIX_2'],
            'lastFour' => $_ENV['ROCKETGATE_CARD_LAST_FOUR_2']
        ];

        $cardNumber = CreditCardNumber::createObfuscated($data);
        $this->assertSame(self::OBFUSCATED_STRING, $cardNumber->cardNumber());
    }
}
