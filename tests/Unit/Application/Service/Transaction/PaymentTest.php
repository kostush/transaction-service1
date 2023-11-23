<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class PaymentTest extends UnitTestCase
{
    /**
     * @test
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_without_method_should_throw_missing_credit_card_information_exception()
    {
        $information = $this->createMock(NewCreditCardInformation::class);
        $this->expectException(MissingChargeInformationException::class);
        new Payment(null, $information);
    }
}
