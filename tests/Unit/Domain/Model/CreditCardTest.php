<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\CreditCard;
use Tests\UnitTestCase;

class CreditCardTest extends UnitTestCase
{
    const MIR = 'mir';

    const UNIONPAY = 'unionpay';

    const MASTERCARD = 'mastercard';

    /**
     * @test
     * @return void
     */
    public function it_should_validate_mir_credit_card(): void
    {
        $fakeMirCC = '2204575340096016';
        $result    = CreditCard::validCreditCard($fakeMirCC);
        $this->assertTrue($result['valid']);
        $this->assertEquals($result['type'], self::MIR);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_validate_old_range_unionpay_credit_card(): void
    {
        $fakeUnionpayCC = '6270472223445660';
        $result         = CreditCard::validCreditCard($fakeUnionpayCC);
        $this->assertTrue($result['valid']);
        $this->assertEquals($result['type'], self::UNIONPAY);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_validate_new_range_unionpay_credit_card(): void
    {
        $fakeUnionpayCC = '8164773234719868263';
        $result         = CreditCard::validCreditCard($fakeUnionpayCC);
        $this->assertTrue($result['valid']);
        $this->assertEquals($result['type'], self::UNIONPAY);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_validate_master_credit_card(): void
    {
        $masterCreditCardNumber = $this->faker->creditCardNumber('MasterCard');
        $result                 = CreditCard::validCreditCard($masterCreditCardNumber);
        $this->assertTrue($result['valid']);
        $this->assertEquals($result['type'], self::MASTERCARD);
    }
}
