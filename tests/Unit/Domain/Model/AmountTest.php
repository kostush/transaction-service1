<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class AmountTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function create_should_throw_an_exception_when_not_positive_amount_is_provided()
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createAmount(['amount' => -10]);
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function create_should_throw_an_exception_when_not_float_amount_is_provided()
    {
        $this->expectException(\TypeError::class);

        $this->createAmount(['amount' => 'notAFloat']);
    }

    /**
     * @test
     * @depends create_should_throw_an_exception_when_not_float_amount_is_provided
     * @depends create_should_throw_an_exception_when_not_positive_amount_is_provided
     * @return Amount
     * @throws \Exception
     */
    public function create_should_return_amount_when_int_amount_is_provided()
    {
        $amount = $this->createAmount(['amount' => 10]);

        $this->assertInstanceOf(Amount::class, $amount);

        return $amount;
    }

    /**
     * @test
     * @depends create_should_throw_an_exception_when_not_float_amount_is_provided
     * @depends create_should_throw_an_exception_when_not_positive_amount_is_provided
     * @return Amount
     * @throws \Exception
     */
    public function create_should_return_amount_when_correct_amount_is_provided()
    {
        $amount = $this->createAmount();

        $this->assertInstanceOf(Amount::class, $amount);

        return $amount;
    }

    /**
     * @test
     * @depends create_should_return_amount_when_correct_amount_is_provided
     * @param Amount $amount Amount object
     * @return void
     * @throws \Exception
     */
    public function amount_should_return_true_when_equals_amount(Amount $amount)
    {
        $equalAmount = $this->createAmount(['amount' => $amount->value()]);

        $this->assertTrue($amount->equals($equalAmount));
    }

    /**
     * @test
     * @depends create_should_return_amount_when_correct_amount_is_provided
     * @param Amount $amount Amount object
     * @return void
     * @throws \Exception
     */
    public function amount_should_return_false_when_equals_amount(Amount $amount)
    {
        $equalAmount = $this->createAmount(['amount' => 99.99]);

        $this->assertFalse($amount->equals($equalAmount));
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_with_invalid_amount_should_throw_missing_charge_information_exception()
    {
        $this->expectException(InvalidChargeInformationException::class);
        Amount::create((float) - 1);
    }
}
