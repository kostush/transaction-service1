<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RocketGateCardHash;
use Tests\UnitTestCase;

class PaymentTemplateInformationTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     * @throws \ReflectionException
     */
    public function it_should_return_a_payment_template_information_object(): array
    {
        $rocketgateCardHash = $this->createMock(RocketGateCardHash::class);
        $paymentTemplate    = PaymentTemplateInformation::create($rocketgateCardHash);

        $this->assertInstanceOf(PaymentTemplateInformation::class, $paymentTemplate);
        return [$rocketgateCardHash, $paymentTemplate];
    }

    /**
     * @test
     * @param array $data The data array
     * @depends it_should_return_a_payment_template_information_object
     * @return void
     */
    public function it_should_contain_the_correct_object(array $data): void
    {
        list($rocketgateCardHash, $paymentTemplate) = $data;
        $this->assertSame($rocketgateCardHash, $paymentTemplate->rocketGateCardHash());
    }

    /**
     *
     * @test
     * @return void
     */
    public function it_should_throw_exception_for_invalid_argument(): void
    {
        $this->expectException(\TypeError::class);
        PaymentTemplateInformation::create('invalid');
    }
}
