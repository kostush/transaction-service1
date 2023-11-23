<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Information;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use Tests\UnitTestCase;

class ExistingCreditCardInformationTest extends UnitTestCase
{
    private $cardHash = 'm77xlHZiPKVsF9p1/VdzTb+CUwaGBDpuSRxtcb7+j24=';
    /**
     * @test
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @return ExistingCreditCardInformation
     */
    public function it_should_return_an_information_object(): ExistingCreditCardInformation
    {
        $existingCreditCardInformation = new ExistingCreditCardInformation(
            $this->cardHash
        );
        $this->assertInstanceOf(Information::class, $existingCreditCardInformation);

        return $existingCreditCardInformation;
    }

    /**
     * @test
     * @depends it_should_return_an_information_object
     * @param ExistingCreditCardInformation $cardInformation The card information
     * @return void
     */
    public function it_should_return_an_object_with_the_correct_card_hash_property(
        ExistingCreditCardInformation $cardInformation
    ): void {
        $this->assertSame($this->cardHash, $cardInformation->cardHash());
    }

    /**
     * @test
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function it_should_throw_an_exception_if_a_card_hash_is_not_provided()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new ExistingCreditCardInformation(null);
    }
}
