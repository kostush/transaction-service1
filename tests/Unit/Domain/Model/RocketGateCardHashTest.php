<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateCardHash;
use Tests\UnitTestCase;

class RocketGateCardHashTest extends UnitTestCase
{
    private $string = 'gjDmpe8SunkzLJke1rUm2HBeZdQ2rp6HtfoyzXU6ooum';

    /**
     * @test
     * @return RocketGateCardHash
     * @throws \Exception
     */
    public function it_should_return_a_rocketgate_card_hash_object(): RocketGateCardHash
    {
        $cardHash = RocketGateCardHash::create($this->string);

        $this->assertInstanceOf(RocketGateCardHash::class, $cardHash);
        return $cardHash;
    }

    /**
     * @test
     * @param RocketGateCardHash $cardHash Card hash object
     * @depends it_should_return_a_rocketgate_card_hash_object
     * @return void
     */
    public function it_should_contain_the_correct_value(RocketGateCardHash $cardHash): void
    {
        $this->assertSame($cardHash->value(), $this->string);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_exception_for_invalid_strings(): void
    {
        $this->expectException(InvalidCreditCardInformationException::class);
        RocketGateCardHash::create('asdf');
    }
}
